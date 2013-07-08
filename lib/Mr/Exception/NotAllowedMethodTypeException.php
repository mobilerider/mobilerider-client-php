<?php 

namespace Mr\Exception;

class NotAllowedMethodTypeException extends MrException
{
    public function __construct(array $disallowedMethods)
    {
        parent::__construct('Following request methods are NOT allowed by this client: ' . implode(', ', $disallowedMethods))
    }
}