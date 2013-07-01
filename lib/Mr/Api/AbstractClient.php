<?php

namespace Mr\Api;

abstract class AbstractClient implements ClientInterface
{
	const METHOD_GET = 'get';
	const METHOD_POST = 'post';
	const METHOD_PUT = 'put';
	const METHOD_DELETE = 'delete';

	const DATA_TYPE_JSON = 'json';
	const DATA_TYPE_HTML = 'html';
	const DATA_TYPE_TEXT = 'text';

	public abstract function get($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public abstract function post($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public abstract function put($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public abstract function delete($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);

	public function request($method, $path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON)
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