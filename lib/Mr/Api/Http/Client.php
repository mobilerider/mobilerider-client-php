<?php 

namespace Mr\Api\Http;

use Mr\Api\ClientInterface;
use Mr\Api\AbstractClient;

class Client extends AbstractClient implements ClientInterface
{
	protected $_host;
	protected $_username;
	protected $_password;

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

	protected function call($method, $path, $parameters, $headers, $responseType)
	{
		$request = new Request($this->getUrl($path), $method, $this->_username, $this->_password, $responseType);
		$request->setParameters($parameters);
		$request->setHeaders($headers);

		$response = $request->send();
		
		return $response->getContent();
	}

	public function get($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		return $this->call(AbstractClient::METHOD_GET, $path, $parameters, $headers, $responseType);
	}	

	public function post($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		return $this->call(AbstractClient::METHOD_POST, $path, $parameters, $headers, $responseType);
	}

	public function put($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		return $this->call(AbstractClient::METHOD_PUT, $path, $parameters, $headers, $responseType);
	}

	public function delete($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		return $this->call(AbstractClient::METHOD_DELETE, $path, $parameters, $headers, $responseType);
	}
}