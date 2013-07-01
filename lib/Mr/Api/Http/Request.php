<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;

class Request
{
	protected $_httpRequest;
	protected $_httpResponse;
	protected $_parameters = array();
	protected $_headers = array();
	protected $_responseType;
    protected $_method;

	public function __construct($url, $method, $username, $password, $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		$this->_responseType = $responseType;
        $this->_method = $method;

		$this->_httpRequest = new \HTTP_Request2($url, $this->getTranslateMethod());
		$this->_httpRequest->setAuth($username, $password, \HTTP_Request2::AUTH_DIGEST);
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

    public function setParameters(array $parameters)
    {
    	$this->_parameters = array_merge($this->_parameters, $parameters);
    }

    public function setHeaders(array $headers)
    {
    	$this->_headers = array_merge($this->_headers, $headers);
    }

	public function addHeader($name, $value)
	{
		$this->_headers[$name] = $value;
	}

	public function send()
	{
		$this->_httpRequest->setHeader($this->_headers);
		$this->_httpResponse = $this->_httpRequest->send();

		return new Response($this->_httpResponse);
	}
}