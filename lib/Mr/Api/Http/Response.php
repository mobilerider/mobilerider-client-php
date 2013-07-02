<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;
use Mr\Api\Util\CommonUtils;

class Response
{
    protected $_httpResponse;
    protected $_dataType;

    public function __construct($httpResponse, $dataType = AbstractClient::DATA_TYPE_JSON)
    {
        $this->_dataType = $dataType;
        $this->_httpResponse = $httpResponse;
    }

    public function getContent()
    {
        $content = $this->_httpResponse->getBody();

        switch ($this->_dataType) {
            case AbstractClient::DATA_TYPE_JSON:
                return CommonUtils::decodeJson($content);
            default:
                return $content;
        }
    }
}