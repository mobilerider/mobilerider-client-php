<?php 

namespace Mr\Api\Http;

use Mr\Api\ClientInterface;
use Mr\Api\AbstractClient;

/** 
 * Client Class file
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
 * Client Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Client extends AbstractClient implements ClientInterface
{
    /**
    * var string Server Host for all requests
    */
    protected $_host;
    /**
    * var string Username or application Id for all requests
    */
    protected $_username;
    /**
    * var string Password or secret word for all requests
    */
    protected $_password;
    /**
    * var array Mock responses for test purposes
    */
    protected $_responses = array();
    /**
    * var boolean Sets to TRUE if request can throw exception with mock responses
    */
    protected $_useExceptionResponse = false;
    /**
    * var Mr\Api\Http\Request
    */
    protected $_request;
    /**
    * var Mr\Api\Http\Response
    */
    protected $_response;

    public function __construct($host, $username, $password)
    {
        $this->_host = $host;
        $this->_username = $username;
        $this->_password = $password;
    }

    protected function getUrl($path)
    {
        return sprintf('http://%s/%s', $this->_host, $path);
    }

    public function addResponse($status = Response::STATUS_OK, $url = '', $content = '')
    {
        $phrase = Response::getPhraseStatus($status);
        $response = "HTTP/1.1 {$status} {$phrase}\r\n Connection: close\r\n\r\n{$content}";
        
        if (empty($url)) {
            $this->_responses[] = $response;
        } else {
            $this->_responses[] = array($response, $url);
        }
    }

    public function addExceptionReponse()
    {
        $this->_useExceptionResponse;
    }

    public function isMock()
    {
        return $this->_useExceptionResponse || !empty($this->_responses);
    }

    protected function call($method, $path, $parameters, $headers, $dataType)
    {
        $this->_request = new Request($this->getUrl($path), $method, $this->_username, $this->_password, $dataType);
        $this->_request->setHeaders($headers);
        $this->_request->setParameters($parameters);
        $this->_request->setResponses($this->_responses, $this->_useExceptionResponse);

        $this->_response = $this->_request->send();
        
        return $this->_response->getContent();
    }

    /**
    * Returns last request sent by this client
    *
    * @return Mr\Api\Http\Request
    */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
    * Returns last response received by this client
    *
    * @return Mr\Api\Http\Response
    */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
    * {@inheritdoc }
    */
    public function get($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        return $this->call(AbstractClient::METHOD_GET, $path, $parameters, $headers, $dataType);
    }   
    
    /**
    * {@inheritdoc }
    */    
    public function post($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        return $this->call(AbstractClient::METHOD_POST, $path, $parameters, $headers, $dataType);
    }

    /**
    * {@inheritdoc }
    */
    public function put($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        return $this->call(AbstractClient::METHOD_PUT, $path, $parameters, $headers, $dataType);
    }

    /**
    * {@inheritdoc }
    */
    public function delete($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        return $this->call(AbstractClient::METHOD_DELETE, $path, $parameters, $headers, $dataType);
    }
}