<?php 

namespace Mr\Exception;

class InvalidFieldException extends MrException
{
    public function __construct($field, $data, array $validator = array())
    {
        parent::__construct('Invalid data: < ' . $data . ' > in field: ' . $field . ', according to validator: ' . print_r($validator, true));
    }
}