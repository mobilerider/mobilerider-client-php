<?php

namespace Mr\Api\Http;

use Mr\Api\AbstractClient;
use Mr\Api\Util\CommonUtils;

/** 
 * Response Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * Response Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Http
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
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