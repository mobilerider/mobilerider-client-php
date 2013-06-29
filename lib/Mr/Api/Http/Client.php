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

	public function get($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		$url = sprintf('http://%s/%s', $this->_host, $path);

		$request = new Request($url, $this->_username, $this->_password, $responseType);
		$request->setParameters($parameters);
		$request->setHeaders($headers);

		$response = $request->send();
		
		return $response->getContent();
	}	

	public function post($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		// Implement this
	}

	public function put($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		// Implement this
	}

	public function delete($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		// Implement this
	}
}