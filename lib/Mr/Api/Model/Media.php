<?php 

namespace Mr\Api\Model;

use Mr\Api\Util\Validator;

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

    public function getValidators()
    {
    	return array(
    		'encoderPrimaryIp' => array(
    			Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
    			Validator::TYPES => array(
    				Validator::MODIFIERS => array(Validator::MODIFIER_IP)
    			)
    		),
    		'encoderBackupIp' => array(
    			Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
    			Validator::TYPES => array(
    				Validator::MODIFIERS => array(Validator::MODIFIER_IP)
    			)
    		),
    		'encoderPassword' => array(
    			Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
    		),
    		'bitrates' => array(
    			Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
    			Validator::TYPES => array(
    				Validator::TYPE => Validator::TYPE_ARRAY,
    				Validator::MODIFIERS => array(
    					Validator::MODIFIER => Validator::MODIFIER_NESTED,
    					Validator::MODIFIER_VALIDATORS => array(
    						Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
    						Validator::TYPES => array(
    							Validator::TYPE => Validator::TYPE_INT,
			    				Validator::MODIFIERS => array(Validator::MODIFIER_POSITIVE)
			    			)
    					)
    				)
    			)
    		)
    	);
    }
}