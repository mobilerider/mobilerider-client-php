<?php

namespace Mr\Exception;

/** 
 * InvalidDataOperationException Class file
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
 * InvalidDataOperationException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class InvalidDataOperationException extends MrException
{
    public function __construct($message, $op)
    {
        parent::__construct('Invalid data trying to: ' . $op . ' with message: ' . $message);
    }
}