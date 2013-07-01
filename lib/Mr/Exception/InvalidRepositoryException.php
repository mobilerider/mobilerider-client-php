<?php

namespace Mr\Exception;

class InvalidRepositoryException extends MrException
{
	public function __construct()
	{
		parent::__construct('Invalid Repository');
	}
}