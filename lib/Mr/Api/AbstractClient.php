<?php

namespace Mr\Api;

/** 
 * AbstractClient Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * AbstractClient Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
abstract class AbstractClient implements ClientInterface
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_PUT = 'put';
    const METHOD_DELETE = 'delete';

    const DATA_TYPE_NONE = 'none';
    const DATA_TYPE_JSON = 'json';
    const DATA_TYPE_HTML = 'html';
    const DATA_TYPE_TEXT = 'text';

    /**
    * Creates and sends a request using GET method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be send within the url as query vars.
    * @param array $headers Header values to be included on the request.
    * @param string $dataType Data type of the transaction request parameters and response content 
    * will be parsed / econded with given format, default is JSON (Only one implemented).
    */
    public abstract function get($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    
    /**
    * Creates and sends a request using POST method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param string $dataType Data type of the transaction request parameters and response content 
    * will be parsed / econded with given format, default is JSON (Only one implemented).
    */
    public abstract function post($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    
    /**
    * Creates and sends a request using PUT method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param string $dataType Data type of the transaction request parameters and response content 
    * will be parsed / econded with given format, default is JSON (Only one implemented).
    */
    public abstract function put($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    
    /**
    * Creates and sends a request using DELETE method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param string $dataType Data type of the transaction request parameters and response content 
    * will be parsed / econded with given format, default is JSON (Only one implemented).
    */
    public abstract function delete($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);

    /**
    * Creates and sends a request.
    *
    * @param string $method Request method type.
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param string $dataType Data type of the transaction request parameters and response content 
    * will be parsed / econded with given format, default is JSON (Only one implemented).
    */
    public function request($method, $path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        $args = func_get_args();
        array_shift($args);

        switch ($method) {
            case self::METHOD_GET:
            case self::METHOD_POST:
            case self::METHOD_GET:
            case self::METHOD_PUT:
                return call_user_func_array(array($this, $method), $args);
            default:
                throw new Exception("Invalid method");
        }
    }
}