<?php

namespace MrTest\Api\Util;

use Mr\Api\Util\Validator;


class ValidatorTest extends \PHPUnit_Framework_TestCase 
{
	protected $requiredValidator = array(
		Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
	);

	protected $requiredNestedValidator = array(
		Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED),
		Validator::MODIFIERS => array(
			Validator::MODIFIER => Validator::MODIFIER_NESTED,
			Validator::MODIFIER_VALIDATORS => array(
				Validator::CONSTRAINTS => array(Validator::CONSTRAINT_REQUIRED)
			)
		)
	);

	public function testConstraints()
	{
		$value = null;
		$this->assertFalse(Validator::validate($value, $this->requiredValidator));

		$value = 'not empty';
		$this->assertTrue(Validator::validate($value, $this->requiredValidator));

		$value = null;
		$this->assertFalse(Validator::validate($value, $this->requiredNestedValidator));

		$value = 'not empty'; // Value must be an array if includes a nested validator
		$this->assertFalse(Validator::validate($value, $this->requiredNestedValidator));

		$value = array(); // Array must contains at least one item if nested validator is used
		$this->assertFalse(Validator::validate($value, $this->requiredNestedValidator));

		$value = array('not empty');
		$this->assertTrue(Validator::validate($value, $this->requiredNestedValidator));
	}
}