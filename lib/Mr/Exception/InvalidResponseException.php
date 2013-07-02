<?php

namespace Mr\Exception;

class InvalidResponseException extends MrException
{
    public function __construct()
    {
        parent::__construct('Invalid response from api server');
    }
}