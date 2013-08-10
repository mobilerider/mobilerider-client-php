<?php 

namespace Mr\Exception;

class InvalidFieldException extends MrException
{
    public function __construct($field, $message)
    {
        parent::__construct('Invalid field: ' . $field . '. ' . $message);
    }
}