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
class Request extends \HTTP_Request2
{
    protected $_parameters = array();
    protected $_dataType = AbstractClient::DATA_TYPE_JSON;

    public function __construct($url, $method = AbstractClient::METHOD_GET, $config = array())
    {
        if (isset($config['dataType'])) {
            $this->_dataType = $config['dataType'];
            unset($config['dataType']);
        }

        parent::__construct($url, $method, $config);
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

    /**
    * Sets a new parameter to the request.
    * It can be a single parameter key-pair of name and value 
    * or an array of parameters.
    *
    * @param string | array $name
    * @param mixed $value
    */
    public function setParameter($name, $value = null)
    {
        if (is_array($name)) {
            $this->_parameters = array_merge($this->_parameters, $name);
        } else {
            $this->{$name} = $value;
        }
    }

    /**
    * {@inheritdoc }
    */ 
    public function send()
    {
        if ($this->method == AbstractClient::METHOD_GET) {
            $this->getUrl()->setQueryVariables($this->_parameters);
        } else if (in_array($this->method, array(AbstractClient::METHOD_POST, AbstractClient::METHOD_PUT))) {
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

            if ($this->method == AbstractClient::METHOD_POST) {
                $this->addPostParameter($params);
            } else {
                $this->setBody(new \HTTP_Request2_MultipartBody($params, array()));
            }
        }

        $httpResponse = parent::send();

        return new Response($httpResponse);
    }
}