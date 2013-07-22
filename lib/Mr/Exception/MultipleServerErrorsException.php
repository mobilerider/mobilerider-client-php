<?php

namespace Mr\Exception;

use Mr\Api\Model\ApiObject;
use Mr\Api\AbstractClient;

/** 
 * MultipleServerErrorsException Class file
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
 * MultipleServerErrorsException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class MultipleServerErrorsException extends MrException
{
    const MSG_TEMPLATE = 'Error: %s, on %s: %d';

    public function __construct($objects, $method)
    {
        $messages = array();

        if ($method == AbstractClient::METHOD_POST) {
            foreach ($objects as $key => $value) {
                if (!$value instanceof ApiObject) {
                    $messages[] = sprintf(self::MSG_TEMPLATE, $value, 'index', $key);
                }
            }
        } else if ($method == AbstractClient::METHOD_PUT) {
            foreach ($objects as $value) {
                if (isset($value->index)) {
                    $messages[] = sprintf(self::MSG_TEMPLATE, $value->text, 'index', $value->index);
                } else {
                    $messages[] = sprintf(self::MSG_TEMPLATE, $value->text, 'object id', $value->id);
                }
            }
        } else {
            $messages = $objects;
        }

        $message = "Multiple errors ocurred during this {$method} action. \r\n";

        foreach ($messages as $msg) {
            $message .= $msg . "\r\n";
        }

        parent::__construct($message);
    }
}
