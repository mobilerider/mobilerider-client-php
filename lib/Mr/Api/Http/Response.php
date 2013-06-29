<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;

class Response
{
	protected $_httpResponse;

	public function __construct($httpResponse, $responseType = AbstractClient::DATA_TYPE_JSON)
	{
		$this->_responseType = $responseType;
		$this->_httpResponse = $httpResponse;
	}

	public function getContent()
	{
		$content = $this->_httpResponse->getBody();

		switch ($this->_responseType) {
			case AbstractClient::DATA_TYPE_JSON:
				return json_decode($content);
			default:
				return $content;
		}
	}
}