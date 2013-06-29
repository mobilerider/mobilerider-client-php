<?php

namespace Mr\Api;

interface ClientInterface
{
	public function get($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public function post($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public function put($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public function delete($path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
	public function request($method, $path, array $parameters = array(), array $headers = array(), $responseType = AbstractClient::DATA_TYPE_JSON);
}