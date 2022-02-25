<?php

namespace Greenhouse\GreenhouseToolsPhp\Tests\Clients\Mocks;

/**
 * This mocks a Guzzle response object. That's all. And currently only the headers in order to test the
 * link setters in GuzzleClient.
 */
class MockGuzzleResponse
{
    public $headers;
    
    public function getHeader($type)
    {
        return $this->headers;
    }
}