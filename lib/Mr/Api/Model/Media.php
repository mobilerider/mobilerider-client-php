<?php 

namespace Mr\Api\Model;

/** 
 * Media Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Model
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * Media Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Model
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Media extends ApiObject
{
    public function getStringField()
    {
        return 'title';
    }
}