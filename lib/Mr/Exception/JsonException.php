<?php

namespace Mr\Exception;

/** 
 * JsonException Class file
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
 * JsonException Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Exception
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class JsonException extends MrException
{
    public function __construct($jsonError, $json = '')
    {
        switch ($jsonError) {
            case JSON_ERROR_NONE:
                $message = 'OK';
            break;
            case JSON_ERROR_DEPTH:
                $message = 'Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                $message = 'Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                $message = 'Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                $message = 'Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
            break;
            default:
                $message = 'Unknown error';
            break;
        }

        parent::__construct('JSON Error: '. $message . '. JSON Data: ' . $json);
    }
}