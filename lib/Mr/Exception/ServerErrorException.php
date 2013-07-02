<?php

namespace Mr\Exception;

/** 
 * ServerErrorException Class file
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
 * ServerErrorException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez8402@gmail.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class ServerErrorException extends MrException
{
    public function __construct($status)
    {
        parent::__construct('Server ERROR: ' . $status);
    }
}