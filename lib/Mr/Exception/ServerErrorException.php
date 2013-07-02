<?php

namespace Mr\Exception;

class ServerErrorException extends MrException
{
    public function __construct($status)
    {
        parent::__construct('Server ERROR: ' . $status);
    }
}