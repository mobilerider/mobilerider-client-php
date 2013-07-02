<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;
use Mr\Api\Util\CommonUtils;

/** 
 * Response Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * Response Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Response
{
    const STATUS_OK = 200;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_SERVER_ERROR = 500;

    protected $_httpResponse;
    protected $_dataType;

    public function __construct($httpResponse, $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        $this->_dataType = $dataType;
        $this->_httpResponse = $httpResponse;
    }

    public function isSuccessful()
    {
        $this->getStatus == self::STATUS_OK;
    }

    public function getStatus()
    {
        $this->_httpResponse->getStatus();
    }

    public function getContent()
    {
        $content = $this->_httpResponse->getBody();

        switch ($this->_dataType) {
            case AbstractClient::DATA_TYPE_JSON:
                return CommonUtils::decodeJson($content);
            default:
                return $content;
        }
    }

    public static function getPhraseStatus($status)
    {
        $phrases = array(
            100 => 'Continue', 
            101 => 'Switching Protocols',
            200 => 'OK', 
            201 => 'Created', 
            202 => 'Accepted', 
            203 => 'Non-Authoritative Information', 
            204 => 'No Content', 
            205 => 'Reset Content', 
            206 => 'Partial Content',
            300 => 'Multiple Choices', 
            301 => 'Moved Permanently', 
            302 => 'Found',  // 1.1 
            303 => 'See Other', 
            304 => 'Not Modified', 
            305 => 'Use Proxy', 
            307 => 'Temporary Redirect',
            400 => 'Bad Request', 
            401 => 'Unauthorized', 
            402 => 'Payment Required', 
            403 => 'Forbidden', 
            404 => 'Not Found', 
            405 => 'Method Not Allowed', 
            406 => 'Not Acceptable', 
            407 => 'Proxy Authentication Required', 
            408 => 'Request Timeout', 
            409 => 'Conflict', 
            410 => 'Gone', 
            411 => 'Length Required', 
            412 => 'Precondition Failed', 
            413 => 'Request Entity Too Large', 
            414 => 'Request-URI Too Long', 
            415 => 'Unsupported Media Type', 
            416 => 'Requested Range Not Satisfiable', 
            417 => 'Expectation Failed',
            500 => 'Internal Server Error', 
            501 => 'Not Implemented', 
            502 => 'Bad Gateway', 
            503 => 'Service Unavailable', 
            504 => 'Gateway Timeout', 
            505 => 'HTTP Version Not Supported', 
            509 => 'Bandwidth Limit Exceeded', 
        );

        return array_key_exists($status, $phrases) ? $phrases[$status] : '';
    }
}