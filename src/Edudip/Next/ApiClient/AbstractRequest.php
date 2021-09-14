<?php

namespace Edudip\Next\ApiClient;

/**
 * @author Marc Steinert <m.steinert@edudip.com>
 * @copyright edudip GmbH
 * @package edudip next Api Client
 */

use Edudip\Next\ApiClient\Error\AuthenticationException;
use Edudip\Next\ApiClient\Error\ResponseException;

abstract class AbstractRequest
{
    // @var int Request timeout in seconds
    const TIMEOUT = 10;

    // @var string User agent string to send in http requests
    const USER_AGENT = 'edudip/next-api-client (github.com/edudip/next-api-client)';

    protected static function getRequest(string $endpoint, array $params = array())
    {
        return self::makeRequest('GET', $endpoint, $params);
    } 

    protected static function postRequest(string $endpoint, array $params = array())
    {
        return self::makeRequest('POST', $endpoint, $params);
    }

    /**
     * @throws \Edudip\Next\ApiClient\ResponseException
     * @throws \Edudip\Next\ApiClient\AuthenticationException
     */
    protected static function makeRequest($httpVerb, $endpoint, $params = array())
    {
        $apiKey = trim(EdudipNext::getApiKey());

        if (! $apiKey) {
            throw new AuthenticationException('Please provide an API key');
        }

        $httpHeaders = array(
            'Accept: application/json',
            'Authorization: Bearer ' . $apiKey,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::buildEndpointUrl($endpoint));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        switch (strtoupper($httpVerb)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, self::buildPostString($params));
                break;

            case 'GET':
                $query = http_build_query($params, '', '&');
                curl_setopt($ch, CURLOPT_URL, self::buildEndpointUrl($endpoint) . '?' . $query);
                break;
        }

        $responseContents = curl_exec($ch);
        $responseHeaders = curl_getinfo($ch);
        curl_close($ch);

        if (array_key_exists('http_code', $responseHeaders)) {
            if ($responseHeaders['http_code'] === 401) {
                throw new AuthenticationException('API returned http status 401 (unauthorized)');
            }
        }

        $json = json_decode($responseContents, true);

        if ($json === null) {
            throw new ResponseException('API returned non-json response');
        }

        if (array_key_exists('success', $json) && ! $json['success']) {
            if (array_key_exists('error', $json)) {
                throw new ResponseException('API returned an error: ' . is_array($json['error']) ? json_encode($json['error']) : $json['error']);
            } else if (array_key_exists('message', $json)) {
                throw new ResponseException('API returned an error: ' . $json['message']);
            } else {
                throw new ResponseException('API returned an error');
            }
        }

        return $json;
    }

    private static function buildPostString(array $params)
    {
        $postString = '';
        
        foreach ($params as $key => $value) {
            $postString .= $key.'='.$value.'&';
        }

        $postString = rtrim($postString, '&');

        return $postString;
    }

    private static function buildEndpointUrl($endpoint)
    {
        return sprintf(
            '%s/%s',
            EdudipNext::getApiBase(),
            ltrim($endpoint, '/')
        );
    }
}
