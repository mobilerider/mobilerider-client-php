<?php

namespace Mr\Api;

/** 
 * ClientInterface Interface file
 *
 * PHP Version 5.3
 *
 * @category Interface
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * ClientInterface Interface
 *
 * Application interface
 *
 * @category Interface
 * @package  Mr\Api
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
interface ClientInterface
{
    public function get($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    public function post($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    public function put($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    public function delete($path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
    public function request($method, $path, array $parameters = array(), array $headers = array(), $dataType = AbstractClient::DATA_TYPE_JSON);
}