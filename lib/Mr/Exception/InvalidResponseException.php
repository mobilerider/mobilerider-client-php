<?php

namespace Mr\Exception;

/** 
 * InvalidResponseException Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * InvalidResponseException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class InvalidResponseException extends MrException
{
    public function __construct()
    {
        parent::__construct('Invalid response from api server');
    }
}