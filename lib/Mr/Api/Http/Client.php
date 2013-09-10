<?php 

namespace Mr\Api\Http;

use Mr\Api\ClientInterface;
use Mr\Api\AbstractClient;
use Mr\Api\ClientAdapterInterface;
use Mr\Api\Http\Adapter\BaseAdapter;
use Mr\Exception\InvalidTypeException;
use Mr\Exception\InvalidDataOperationException;

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
    * var Mr\Api\Http\Request
    */
    protected $_request;
    /**
    * var Mr\Api\Http\Response
    */
    protected $_response;
    /**
    * var Mr\Api\ClientAdapterInterface
    */
    protected $_adapter;
    /**
    * var array
    */
    protected $_config = array(
        'dataType' => AbstractClient::DATA_TYPE_JSON
    );

    public function __construct($host, $username = '', $password = '', array $config = array(), ClientAdapterInterface $adapter = null)
    {
        $this->_host = $host;
        $this->_username = $username;
        $this->_password = $password;
        $this->_config = array_merge($this->_config, $config);
        $this->setAdapter($adapter);
    }

    public function setAdapter(ClientAdapterInterface $adapter = null)
    {
        if (!empty($adapter) && !($adapter instanceof \HTTP_Request2_Adapter)) {
            throw new InvalidTypeException('\HTTP_Request2_Adapter', $adapter);
        }

        $this->_adapter = $adapter;
    }

    public function setGlobalConfig($name, $value)
    {
        $this->_config[$name] = $value;
    }

    protected function getUrl($path)
    {
        $protocol = 'https://';

        if (preg_match('/^https?:\/\/[\w]+/', $this->_host)) {
            $protocol = '';
        }

        $url = sprintf('%s%s/%s', $protocol, $this->_host, $path);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidDataOperationException("Invalid Url", 'Computing Url on client');
        }

        return $url;
    }

    protected function call($method, $path, $parameters, $headers, $config)
    {
        $config = array_merge($this->_config, $config);

        $this->_request = new Request($this->getUrl($path), $method, $config);
        
        if (!empty($this->_username)) {
            $this->_request->setAuth($this->_username, $this->_password, \HTTP_Request2::AUTH_BASIC);
        }

        if (!empty($this->_adapter)) {
            $this->_request->setAdapter($this->_adapter);
        }

        $this->_request->setHeader($headers);
        $this->_request->setParameter($parameters);

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
    public function get($path, array $parameters = array(), array $headers = array(), $config = array())
    {
        return $this->call(AbstractClient::METHOD_GET, $path, $parameters, $headers, $config);
    }   
    
    /**
    * {@inheritdoc }
    */    
    public function post($path, array $parameters = array(), array $headers = array(), $config = array())
    {
        return $this->call(AbstractClient::METHOD_POST, $path, $parameters, $headers, $config);
    }

    /**
    * {@inheritdoc }
    */
    public function put($path, array $parameters = array(), array $headers = array(), $config = array())
    {
        return $this->call(AbstractClient::METHOD_PUT, $path, $parameters, $headers, $config);
    }

    /**
    * {@inheritdoc }
    */
    public function delete($path, array $parameters = array(), array $headers = array(), $config = array())
    {
        return $this->call(AbstractClient::METHOD_DELETE, $path, $parameters, $headers, $config);
    }
}