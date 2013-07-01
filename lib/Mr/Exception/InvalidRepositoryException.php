<?php

class InvalidRepositoryException
{
	public function __construct()
	{
		parent::__construct('Invalid Repository');
	}
}