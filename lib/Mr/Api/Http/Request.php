<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;
use Mr\Api\Util\CommonUtils;

/** 
 * Request Class file
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
 * Request Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Request
{
    protected $_httpRequest;
    protected $_httpResponse;
    protected $_parameters = array();
    protected $_headers = array();
    protected $_dataType;
    protected $_method;
    protected $_responses = array();
    protected $_useExceptionResponse = false;

    public function __construct($url, $method, $username, $password, $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        $this->_dataType = $dataType;
        $this->_method = $method;

        $this->_httpRequest = new \HTTP_Request2($url, $this->getTranslateMethod());
        $this->_httpRequest->setAuth($username, $password, \HTTP_Request2::AUTH_DIGEST);
    }

    /**
    * Returns request method
    *
    * @return string
    */
    public function getMethod()
    {
        return $this->_method;
    }

    public function getTranslateMethod()
    {
        switch ($this->_method) {
            case AbstractClient::METHOD_POST:
                $method = \HTTP_Request2::METHOD_POST;
                break;
            case AbstractClient::METHOD_PUT:
                $method = \HTTP_Request2::METHOD_PUT;
                break;
            case AbstractClient::METHOD_DELETE:
                $method = \HTTP_Request2::METHOD_DELETE;
                break;
            default:
                $method = \HTTP_Request2::METHOD_GET;
                break;
        }

        return $method;
    }

    /**
     * <b>Magic method</b>. Returns value of specified field
     *
     * @param string $name Parameter name
     *
     * @return mixed
     */

    public function __get($name)
    {
        return array_key_exists($name, $this->_parameters) ? $this->_parameters[$name] : null;
    }

    /**
     * <b>Magic method</b>. Sets value of field in row
     *
     * @param string $name  parameter name
     * @param mixed  $value parameter value
     */
    public function __set($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    public function setHeaders(array $headers)
    {
        $this->_headers = array_merge($this->_headers, $headers);
    }

    public function setParameters(array $parameters)
    {
        $this->_parameters = array_merge($this->_parameters, $parameters);
    }

    public function setResponses(array $responses, $useExceptionResponse)
    {
        $this->_responses = array_merge($this->_responses, $responses);
        $this->_useExceptionResponse = $useExceptionResponse;
    }

    public function isMock()
    {
        return $this->_useExceptionResponse || !empty($this->_responses);
    }

    public function send()
    {
        $adapter = null;

        if ($this->isMock()) {
            $adapter = new \HTTP_Request2_Adapter_Mock();

            foreach ($this->_responses as $response) {
                if (is_array($response)) {
                    $adapter->addResponse($response[0], $response[1]);
                } else {
                    $adapter->addResponse($response);
                }
            }

            if ($this->_useExceptionResponse) {
                $adapter->addResponse(new \HTTP_Request2_Exception("Server Mock Response Exception!"));
            }
        }

        if (!empty($adapter)) {
            $this->_httpRequest->setAdapter($adapter);
        }

        $this->_httpRequest->setHeader($this->_headers);

        $params = array();
        foreach ($this->_parameters as $name => $value) {
            switch ($this->_dataType) {
                case AbstractClient::DATA_TYPE_JSON:
                    $params[$name] = CommonUtils::encodeJson($value);
                    break;
                default:
                    $params[$name] = $value;
            }
        }

        if ($this->_method == AbstractClient::METHOD_GET) {
            $this->_httpRequest->getUrl()->setQueryVariables($params);
        } else {
            $this->_httpRequest->addPostParameter($params);
        }

        $this->_httpResponse = $this->_httpRequest->send();

        return new Response($this->_httpResponse);
    }
}