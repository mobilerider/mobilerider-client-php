<?php

namespace Mr\Api\Util;

use Mr\Exception\InvalidFormatException;

class Validator
{
    const CONSTRAINTS = 'constraints';
    const TYPES = 'types';
    const TYPE = 'type';
    const MODIFIERS = 'modifiers';
    const MODIFIER = 'modifier';

    const CONSTRAINT_REQUIRED = 'required';

    const TYPE_INT = 'int';
    const TYPE_ARRAY = 'array';

    const MODIFIER_NESTED = 'nested';
    const MODIFIER_VALIDATORS = 'validators';
    const MODIFIER_MIN_LENGHT = 'min_length';
    const MODIFIER_MAX_LENGHT = 'max_length';
    const MODIFIER_POSITIVE = 'positive';
    const MODIFIER_NEGATIVE = 'negative';
    const MODIFIER_IP = 'ip';

    public static function validate($value, array $validators)
    {
        if (!self::validateConstraints($value, $validators)) {
            return false;
        }

        if (!self::validateTypes($value, $validators)) {
            return false;
        }

        if (!self::validateModifiers($value, $validators)) {
            return false;
        }

        return true;
    }

    private static function validateModifiers($value, $validators)
    {
        if (!is_array($validators) || !isset($validators[self::MODIFIERS])) {
            return true;
        }

        $modifiers = is_array($validators) ? $validators[self::MODIFIERS] : array();
        $modifiers = is_array($modifiers) && (empty($modifiers) || isset($modifiers[0])) ? $modifiers : array($modifiers);

        foreach ($modifiers as $modifier) {
            if (!self::applyModifier($value, $modifier)) {
                return false;
            }
        }

        return true;
    }

    private static function validateConstraints($value, $validators)
    {
        if (!is_array($validators) || !isset($validators[self::CONSTRAINTS])) {
            return true;
        }

        $constraints = isset($validators[self::CONSTRAINTS]) ? $validators[self::CONSTRAINTS] : array();

        foreach ($constraints as $constraint) {
            if (!self::applyConstraint($value, $constraint)) {
                return false;
            }

            self::validateModifiers($value, $constraint);
        }

        return true;
    }

    private static function validateTypes($value, array $validators)
    {
        if (!is_array($validators) || !isset($validators[self::TYPES])) {
            return true;
        }

        $types = $validators[self::TYPES];
        $types = is_array($types) && (empty($types) || isset($types[0])) ? $types : array($types);

        foreach ($types as $type) {
            if (isset($type[self::TYPE])) {
                $typeName = is_array($type) ? $type[self::TYPE] : $type;
                $method = 'is_' . $typeName;

                if (!call_user_func_array($method, array($value))) { //@TODO: Allow support for optional types (several type alternatives)
                    return false;
                }
            }

            self::validateModifiers($value, $type);
        }

        return true;
    }

    private static function applyConstraint($value, $constraint)
    {
        switch ($constraint) {
            case self::CONSTRAINT_REQUIRED:
                return !empty($value);
            default:
                return true;
        }
    }

    private static function applyModifier($value, $modifier)
    {
        $name = is_array($modifier) ? $modifier[self::MODIFIER] : $modifier;

        switch ($name) {
            case self::MODIFIER_NESTED:
                if (!is_array($value) || empty($value)) {
                    return self::validate(null, $modifier[self::MODIFIER_VALIDATORS]);
                }

                foreach ($value as $child) {
                    if (!self::validate($child, $modifier[self::MODIFIER_VALIDATORS])) {
                        return false;
                    }
                }

                return true;
            case self::MODIFIER_IP:
                return filter_var($value, FILTER_VALIDATE_IP);
            case self::MODIFIER_POSITIVE:
                return $value >= 0;
                break;
            default:
                return true;
        }
    }
}