<?php 

namespace Mr\Api\Model;

use Mr\Api\Util\Validator;

/** 
 * Channel Class file
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
 * Channel Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Model
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Channel extends ApiObject
{
    public function getStringField()
    {
        return 'name';
    }

    public function getValidators()
    {
        $validators = array(
            'url' => array(
                Validator::MODIFIERS => array(Validator::MODIFIER_URL)
            )
        );

        return $validators;
    }
}