<?php

namespace Edudip\Next\ApiClient;

/**
 * @author Marc Steinert <m.steinert@edudip.com>
 * @copyright edudip GmbH
 * @package edudip next Api Client
 */

final class EdudipNext
{
    // @var string The edudip next API key to be used to authenticate requests
    public static $apiKey;

    public static $apiBase = 'https://api.edudip-next.com/api';

    /**
     * @return string
     */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @param string $apiKey
     */
    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function getApiBase()
    {
        return self::$apiBase;
    }
}
