<?php 

namespace Mr\Exception;

class InvalidFiltersException extends MrException
{
    public function __construct(array $invalidKeys, array $allowdKeys)
    {
    	$message = 'Some of the supplied filters are invalid. Valid filters are: [' . implode(', ', $allowdKeys) . '].';
    	$message .= ' You supplied: ' . implode(', ', $invalidKeys);

        parent::__construct($message);
    }
}