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
    const TYPE_LIVE = 'Live Video';

    public function getStringField()
    {
        return 'title';
    }

    public function getValidators()
    {
        $validators = array();

        $streamValidators = array(
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
            )
        );

        // Common validators
        switch ($this->type) {
            case self::TYPE_LIVE:
                $validators = array_merge_recursive($validators, array(
                    'bitrates' => array(
                        Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
                        Validator::TYPES => array(
                            Validator::TYPE => Validator::TYPE_ARRAY
                        ),
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
                ));
            default:
                $validators = array_merge_recursive($validators, array());
        }

        // Validators for NEW object
        if ($this->isNew()) {
            switch ($this->type) {
                case self::TYPE_LIVE:
                    $validators = array_merge_recursive($validators, array(
                        $streamValidators
                    ));
                default:
                    $validators = array_merge_recursive($validators, array());
            }
        } else {
            switch ($this->type) {
                case self::TYPE_LIVE:
                    $validators = array_merge_recursive($validators, array(
                        'stream' => array(
                            Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
                            Validator::MODIFIERS => array(
                                Validator::MODIFIER => Validator::MODIFIER_NESTED,
                                Validator::MODIFIER_VALIDATORS => array_merge_recursive(array(
                                    'encoderUsername' => array(
                                        Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
                                    ),
                                    'entrypoints' => array(
                                        Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
                                        Validator::MODIFIERS => array(
                                            Validator::MODIFIER => Validator::MODIFIER_NESTED,
                                            Validator::MODIFIER_VALIDATORS => array(
                                                'Primary' => array(
                                                    Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
                                                ),
                                                'Backup' => array(
                                                    Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
                                                )
                                            )
                                        )
                                    )
                                ), $streamValidators)
                            )
                        )
                    ));
                default:
                    $validators = array_merge_recursive($validators, array());
            }
        }

        return $validators;
    }
}