<?php

namespace Greenhouse\GreenhouseToolsPhp\Tools;

/**
 * Given a JSON String response from the Greenhouse API, Decode it in to objects
 * or arrays.  
 */
class JsonHelper
{
    public static function decodeToObjects($json)
    {
        return json_decode($json, false);
    }
    
    public static function decodeToHash($json)
    {
        return json_decode($json, true);
    }
}
