<?php

namespace Mr\Api;

use Mr\Exception\MrException;

/** 
 * AbstractClient Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez@mobilerider.com>
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
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
abstract class AbstractClient implements ClientInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

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
    * @param array $config List of config key-pair values
    */
    public abstract function get($path, array $parameters = array(), array $headers = array(), $config = array());
    
    /**
    * Creates and sends a request using POST method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param array $config List of config key-pair values
    */
    public abstract function post($path, array $parameters = array(), array $headers = array(), $config = array());
    
    /**
    * Creates and sends a request using PUT method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param array $config List of config key-pair values
    */
    public abstract function put($path, array $parameters = array(), array $headers = array(), $config = array());
    
    /**
    * Creates and sends a request using DELETE method.
    *
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param array $config List of config key-pair values
    */
    public abstract function delete($path, array $parameters = array(), array $headers = array(), $config = array());

    /**
    * Creates and sends a request.
    *
    * @param string $method Request method type.
    * @param string $path Url where to send the request, without host.
    * @param array $parameters Parameter values that will be included as post parameters in request.
    * @param array $headers Header values to be included on the request.
    * @param array $config List of config key-pair values
    */
    public function request($method, $path, array $parameters = array(), array $headers = array(), $config = array())
    {
        $args = func_get_args();
        array_shift($args);

        switch ($method) {
            case self::METHOD_GET:
            case self::METHOD_POST:
            case self::METHOD_PUT:
            case self::METHOD_DELETE:
                return call_user_func_array(array($this, strtolower($method)), $args);
            default:
                throw new MrException("Invalid request method");
        }
    }
}