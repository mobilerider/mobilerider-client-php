<?php

namespace Mr\Exception;

/** 
 * InvalidFormatException Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * InvalidFormatException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class InvalidFormatException extends MrException
{
    public function __construct()
    {
        parent::__construct('Invalid data format');
    }
}
