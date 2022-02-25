<?php

namespace Greenhouse\GreenhouseToolsPhp\Services;

use Greenhouse\GreenhouseToolsPhp\Services\ApiService;
use Greenhouse\GreenhouseToolsPhp\Clients\GuzzleClient;
use Greenhouse\GreenhouseToolsPhp\Services\Exceptions\GreenhouseServiceException;
use Greenhouse\GreenhouseToolsPhp\Tools\HarvestHelper;

/**
 * This class interacts with Greenhouse's Harvest API.
 */
class HarvestService extends ApiService
{
    private $_harvestHelper;
    private $_harvest;
    
    public function __construct($apiKey)
    {
        $this->_apiKey = $apiKey;
        $client = new GuzzleClient(array('base_uri' => self::HARVEST_V1_URL));
        $this->setClient($client);
        $this->_authorizationHeader = $this->getAuthorizationHeader($apiKey);
        $this->_harvestHelper = new HarvestHelper();
    }
    
    public function getHarvest()
    {
        return $this->_harvest;
    }

    public function sendRequest()
    {
        $authHeader = array('Authorization' => $this->_authorizationHeader);
        $allHeaders = array_merge($this->_harvest['headers'], $authHeader);
        $requestUrl = $this->_harvestHelper->addQueryString($this->_harvest['url'], $this->_harvest['parameters']);
        $options = array(
            'headers' => $allHeaders,
            'body' => $this->_harvest['body'],
        );

        return $this->_apiClient->send($this->_harvest['method'], $requestUrl, $options);
    }
    
    /**
     * The following 3 methods are for Harvest Paging. They return paging info from the Link header (if it
     * exists). Paging is complete if 'nextLink' returns nothing.
     */
    public function nextLink()
    {
        return $this->_apiClient->getNextLink();
    }
    
    public function prevLink()
    {
        return $this->_apiClient->getPrevLink();
    }
    
    public function lastLink()
    {
        return $this->_apiClient->getLastLink();
    }
    
    /**
     * In order to keep up to date with changes to the Harvest api and not trigger a re-release of this 
     * package each time a new method is created, the magic Call method is used to construct URLs to the
     * Harvest API.  This will use the called method and the arguments provided to create the proper URL
     * to request the service.  This should return the response from the API on success and raise an 
     * exception on failure.  In most cases, this should be straightforward parsing.
     *
     * 1) getApplications() will transform in to a Get request to "applications"
     * 2) getApplications(array('id' => 12345)) will translate to a GET request to "applications/12345"
     * 3) getScorecardsForApplications(array('id' => 12345)) will translate to 
     *      "applications/12345/scorecards
     */
    public function __call($name, $arguments)
    {
        $args = sizeof($arguments) > 0 ? $arguments[0] : array();
        $this->_harvest = $this->_harvestHelper->parse($name, $args);
        return $this->sendRequest();
    }
    
    /**
     * All methods below this point are methods that don't fit in the standard url format.  Either the 
     * words are not pluralized (application/12345/move instead of moves) or there is an additional word
     * at the end of the URL (application/123/offers/current_offer) which we can't handle in the magic method
     * above.  Standard URLs that fit the common Harvest format will work automatically going forward.  Any
     * exceptions should go below this line.
     */
    
    public function getActivityFeedForCandidate($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getActivityFeedForCandidate', $parameters);
        return $this->_trimUrlAndSendRequest();
    }

    public function postNoteForCandidate($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postActivityFeedForCandidate', $parameters);
        $this->_harvest['url'] = 'candidates/' . $parameters['id'] . '/activity_feed/notes';
        $this->sendRequest();
    }
    
    public function putAnonymizeCandidate($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('putAnonymizeForCandidate', $parameters);
        return $this->_trimUrlAndSendRequest();
    }

    public function getJobPostsForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getJobPostForJob', $parameters);
        return $this->_trimUrlAndSendRequest();
    }
    
    public function getJobStagesForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getStagesForJob', $parameters);
        return $this->sendRequest();
    }
    
    public function getCurrentOfferForApplication($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getOffersForApplication', $parameters);
        $this->_harvest['url'] = $this->_harvest['url'] . '/current_offer';
        return $this->sendRequest();
    }
    
    public function postAdvanceApplication($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postAdvanceForApplication', $parameters);
        return $this->_trimUrlAndSendRequest();
    }
        
    public function postMoveApplication($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postMoveForApplication', $parameters);
        return $this->_trimUrlAndSendRequest();
    }

    public function postTransferApplicationToJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postTransferToJobForApplication', $parameters);
        return $this->_trimUrlAndSendRequest();
    }

    public function postRejectApplication($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postRejectForApplication', $parameters);
        return $this->_trimUrlAndSendRequest();
    }
    
    public function postUnrejectApplication($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postUnrejectForApplication', $parameters);
        return $this->_trimUrlAndSendRequest();
    }
    
    public function putMergeCandidates($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('putMergeCandidate', $parameters);
        $this->_harvest['url'] = 'candidates/merge';
        $this->sendRequest();
    }
    
    /**
     * It is explicitely required to do custom field here because we require the trailing slash, because
     * in this case Sinatra didn't give us an option not to.
     */
    public function getCustomFields($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getCustomFields', $parameters);
        if (!array_key_exists('id', $parameters)) $this->_harvest['url'] = $this->_harvest['url'] . '/';
        return $this->sendRequest();
    }
    
    public function getCustomField($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getCustomFields', $parameters);
        $this->_harvest['url'] = 'custom_field/' . $parameters['id'];
        return $this->sendRequest();
    }
    
    public function getCustomFieldOptionsForCustomField($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getCustomFieldOptionsForCustomField', $parameters);
        $this->_harvest['url'] = 'custom_field/' . $parameters['id'] . '/custom_field_options';
        return $this->sendRequest();
    }

    public function postCustomFieldOptionsForCustomField($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postCustomFieldOptionsForCustomField', $parameters);
        $this->_harvest['url'] = 'custom_field/' . $parameters['id'] . '/custom_field_options';
        $this->sendRequest();
    }

    public function deleteCustomFieldOptionsForCustomField($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('deleteCustomFieldOptionsForCustomField', $parameters);
        $this->_harvest['url'] = 'custom_field/' . $parameters['id'] . '/custom_field_options';
        $this->sendRequest();
    }

    public function patchCustomFieldOptionsForCustomField($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('patchCustomFieldOptionsForCustomField', $parameters);
        $this->_harvest['url'] = 'custom_field/' . $parameters['id'] . '/custom_field_options';
        $this->sendRequest();
    }
    
    public function getEeoc($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getEeoc', $parameters);
        $this->_harvest['url'] = array_key_exists('id', $parameters) ? 'eeoc/' . $parameters['id'] : 'eeoc';
        return $this->sendRequest();
    }
    
    public function putHiringTeamForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('putHiringTeamForJob', $parameters);
        $this->_harvest['url'] = 'jobs/' . $parameters['id'] . '/hiring_team';
        $this->sendRequest();
    }
    
    public function getCandidateTags($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getCandidateTags', $parameters);
        $this->_harvest['url'] = 'tags/candidate';
        return $this->sendRequest();
    }
    
    public function patchEnableUser($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('patchEnableUser', $parameters);
        $this->_harvest['url'] = 'users/' . $parameters['id'] . '/enable';
        $this->sendRequest();
    }
    
    public function patchDisableUser($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('patchDisableUser', $parameters);
        $this->_harvest['url'] = 'users/' . $parameters['id'] . '/disable';
        $this->sendRequest();
    }

    public function getHiringTeamForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getHiringTeamForJob', $parameters);
        return $this->_trimUrlAndSendRequest();
    }
    
    public function postHiringTeamForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('postHiringTeamForJob', $parameters);
        $this->_trimUrlAndSendRequest();
    }

    public function deleteHiringTeamForJob($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('deleteHiringTeamForJob', $parameters);
        $this->_trimUrlAndSendRequest();
    }

    public function getQuestionSetsForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getQuestionSetsForDemographics', $parameters);
        if (array_key_exists('id', $parameters) && $parameters['id']) {
            $this->_harvest['url'] = 'demographics/question_sets/' . $parameters['id'];
        } else {
            $this->_harvest['url'] = 'demographics/question_sets';
        }

        return $this->sendRequest();
    }

    public function getQuestionsForQuestionSetsForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getQuestionsForQuestionSetsForDemographics', $parameters);
        $this->_harvest['url'] = 'demographics/question_sets/' . $parameters['id'] . '/questions';

        return $this->sendRequest();
    }

    public function getAnswerOptionsForQuestionsForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getAnswerOptionsForQuestionsForDemographics', $parameters);
        $this->_harvest['url'] = 'demographics/questions/' . $parameters['id'] . '/answer_options';

        return $this->sendRequest();
    }

    public function getQuestionsForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getQuestionsForDemographics', $parameters);
        if (array_key_exists('id', $parameters) && $parameters['id']) {
            $this->_harvest['url'] = 'demographics/questions/' . $parameters['id'];
        } else {
            $this->_harvest['url'] = 'demographics/questions';
        }

        return $this->sendRequest();
    }

    public function getAnswerOptionsForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getAnswerOptionsForDemographics', $parameters);
        if (array_key_exists('id', $parameters) && $parameters['id']) {
            $this->_harvest['url'] = 'demographics/answer_options/' . $parameters['id'];
        } else {
            $this->_harvest['url'] = 'demographics/answer_options';
        }

        return $this->sendRequest();
    }

    public function getAnswersForDemographics($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getAnswersForDemographics', $parameters);
        if (array_key_exists('id', $parameters) && $parameters['id']) {
            $this->_harvest['url'] = 'demographics/answers/' . $parameters['id'];
        } else {
            $this->_harvest['url'] = 'demographics/answers';
        }

        return $this->sendRequest();
    }

    public function getDemographicAnswersForApplications($parameters=array())
    {
        $this->_harvest = $this->_harvestHelper->parse('getDemographicsForAnswersForApplications', $parameters);
        return $this->sendRequest();
    }

    private function _trimUrlAndSendRequest()
    {
        $this->_harvest['url'] = substr($this->_harvest['url'], 0, -1);
        return $this->sendRequest();
    }
}