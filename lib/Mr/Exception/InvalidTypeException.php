<?php

namespace Mr\Exception;

class InvalidTypeException extends MrException
{
    public function __construct($expected, $actual)
    {
        $expectedType = is_object($expected) ? get_class($expected) : (is_string($expected) ? $expected : gettype($expected));
        $actualType = is_object($actual) ? get_class($actual) : (is_string($actual) ? $actual : gettype($actual));

        parent::__construct("Invalid type or class provided, expected: {$expectedType}, actually provided: {$actualType}");
    }
}