<?php

namespace Mr\Api\Util;

use Mr\Exception\InvalidFormatException;

/**
 * Validator Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Util
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * Validator Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Util
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
class Validator
{
    const CONSTRAINTS = 'constraints';
    const TYPES = 'types';
    const TYPE = 'type';
    const MODIFIERS = 'modifiers';
    const MODIFIER = 'modifier';
    const NONE = 'none';

    const CONSTRAINT_REQUIRED = 'required';
    const CONSTRAINT_NUMERIC_REQUIRED = 'numeric_required';
    const CONSTRAINT_NOT_NULL = 'not_null';

    const TYPE_INT = 'integer';
    const TYPE_ARRAY = 'array';
    const TYPE_NUMERIC = 'numeric';
    const TYPE_OBJECT = 'object';

    const MODIFIER_NESTED = 'nested';
    const MODIFIER_VALIDATORS = 'validators';
    const MODIFIER_MIN_LENGHT = 'min_length';
    const MODIFIER_MAX_LENGHT = 'max_length';
    const MODIFIER_POSITIVE = 'positive';
    const MODIFIER_NEGATIVE = 'negative';
    const MODIFIER_IP = 'ip';
    const MODIFIER_URL = 'url';

    const MSG_VALUE_PREFIX = 'The value %s ';
    const MSG_OK = 'The value is valid';
    const MSG_INVALID_DEFAULT = 'is invalid.';

    public static $messages = array(
        self::CONSTRAINT_REQUIRED => 'is empty',
        self::CONSTRAINT_NUMERIC_REQUIRED => 'is empty',
        self::TYPE_INT => 'is not an integer',
        self::TYPE_ARRAY => 'is not an array',
        self::TYPE_NUMERIC => 'is not numeric',
        self::TYPE_OBJECT => 'is not an object',
        self::MODIFIER_POSITIVE => 'is not a positive number',
        self::MODIFIER_NEGATIVE => 'is not a negative number',
        self::MODIFIER_IP => 'is not a valid Ip',
        self::MODIFIER_URL => 'is not a valid Url',
        self::MODIFIER_NESTED => 'contains invalid values'
    );

    /**
     * Returns invalid message according to given validator and including value string representation
     * Value is included in the message using the method var_export for better redability
     *
     * @param mixed $value Value to include in the message
     * @param string $validator Validator name which the message is related to
     * @return string
     */
    public static function getMessage($value, $validator)
    {
        $message = isset(self::$messages[$validator]) ? self::$messages[$validator] : self::MSG_INVALID_DEFAULT;

        return sprintf(self::MSG_VALUE_PREFIX . $message, var_export($value, true));
    }

    /**
     * Checks if the given value matches given validators.
     *
     * @param mixed $value Value to check
     * @param array $validator Validator list to match with given value
     * @return array Tuple with validation status (boolean) and message (string)
     */
    public static function validate($value, array $validators)
    {
        $valid = true;

        if ($valid) {
            list($valid, $validator) = self::validateConstraints($value, $validators);
        }

        if ($valid) {
            list($valid, $validator) = self::validateTypes($value, $validators);
        }

        if ($valid) {
            list($valid, $validator) = self::validateModifiers($value, $validators);
        }

        return $valid ? array($valid, self::MSG_OK) : array($valid, self::getMessage($value, $validator));
    }

    private static function validateModifiers($value, $validators)
    {
        if (!is_array($validators) || !isset($validators[self::MODIFIERS])) {
            return array(true, self::NONE);
        }

        $modifiers = is_array($validators) ? $validators[self::MODIFIERS] : array();
        $modifiers = is_array($modifiers) && (empty($modifiers) || isset($modifiers[0])) ? $modifiers : array($modifiers);

        foreach ($modifiers as $modifier) {
            // If value is empty none action is taken
            // For value required validation use constraint required
            if (!empty($value) && !self::applyModifier($value, $modifier)) {
                $validator = is_array($modifier) ? $modifier[self::MODIFIER] : $modifier;
                return array(false, $validator);
            }
        }

        return array(true, self::NONE);
    }

    private static function validateConstraints($value, $validators)
    {
        if (!is_array($validators) || !isset($validators[self::CONSTRAINTS])) {
            return array(true, self::NONE);
        }

        $constraints = isset($validators[self::CONSTRAINTS]) ? $validators[self::CONSTRAINTS] : array();

        foreach ($constraints as $constraint) {
            if (!self::applyConstraint($value, $constraint)) {
                return array(false, $constraint);
            }

            list($valid, $modifier) = self::validateModifiers($value, $constraint);

            if (!$valid) {
                return array($valid, $modifier);
            }
        }

        return array(true, self::NONE);
    }

    private static function validateTypes($value, array $validators)
    {
        if (!is_array($validators) || !isset($validators[self::TYPES])) {
            return array(true, self::NONE);
        }

        $types = $validators[self::TYPES];
        $types = (is_array($types) && (empty($types) || isset($types[0]))) ? $types : array($types);

        foreach ($types as $type) {
            // If value is empty no action is taken
            // For value required validation use constraint required
            if (!empty($value) && (!is_array($type) || isset($type[self::TYPE]))) {
                $typeName = is_array($type) ? $type[self::TYPE] : $type;
                $method = 'is' . ucfirst($typeName);

                if (!self::$method($value)) { //@TODO: Allow support for optional types (several type alternatives)
                    return array(false, $typeName);
                }
            }

            list($valid, $modifier) = self::validateModifiers($value, $type);

            if (!$valid) {
                return array($valid, $modifier);
            }
        }

        return array(true, self::NONE);
    }

    private static function applyConstraint($value, $constraint)
    {
        switch ($constraint) {
            case self::CONSTRAINT_REQUIRED:
                return !empty($value);
            case self::CONSTRAINT_NUMERIC_REQUIRED:
                return $value === 0 || !empty($value);
            case self::CONSTRAINT_NOT_NULL:
                return $value !== null;
            default:
                return true;
        }
    }

    private static function applyModifier($value, $modifier)
    {
        $name = is_array($modifier) ? $modifier[self::MODIFIER] : $modifier;

        switch ($name) {
            case self::MODIFIER_NESTED:
                if ((!is_array($value) && !is_object($value)) || empty($value)) {
                    list($valid, $validator) = self::validate(null, $modifier[self::MODIFIER_VALIDATORS]);
                    return $valid;
                }

                if (is_array($value)) {
                    foreach ($value as $child) {
                        list($valid, $validator) = self::validate($child, $modifier[self::MODIFIER_VALIDATORS]);
                        if (!$valid) {
                            return false;
                        }
                    }
                } else if (is_object($value)) {
                    // Assumes validators will be arranged by field names
                    foreach ($modifier[self::MODIFIER_VALIDATORS] as $field => $validator) {
                        list($valid, $validator) = self::validate($value->{$field}, $validator);
                        if (!$valid) {
                            return false;
                        }
                    }
                }

                return true;
            case self::MODIFIER_IP:
                return filter_var($value, FILTER_VALIDATE_IP);
            case self::MODIFIER_POSITIVE:
                return ($value >= 0);
            case self::MODIFIER_NEGATIVE:
                return ($value < 0);
            case self::MODIFIER_URL:
                return filter_var($value, FILTER_VALIDATE_URL);
            default:
                return true;
        }
    }

    public static function isInteger($value)
    {
        return is_numeric($value) && round($value) == $value;
    }

    public static function isArray($value)
    {
        return is_array($value);
    }

    public static function isObject($value)
    {
        return is_object($value);
    }

    public static function isNumeric($value)
    {
        return is_numeric($value);
    }
}