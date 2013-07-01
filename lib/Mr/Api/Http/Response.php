<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;
use Mr\Exception\JsonException;

class Response
{
	protected $_httpResponse;

	public function __construct($httpResponse, $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		$this->_responseType = $responseType;
		$this->_httpResponse = $httpResponse;
	}

	protected function getJSON($content)
	{
		$data = json_decode($content);

		if (JSON_ERROR_NONE != ($jsonError = json_last_error())) {
			throw new JsonException($jsonError);
		}

	    return $data;
	}

	public function getContent()
	{
		$content = $this->_httpResponse->getBody();

		switch ($this->_responseType) {
			case AbstractClient::DATA_TYPE_JSON:
				return $this->getJSON($content);
			default:
				return $content;
		}
	}
}