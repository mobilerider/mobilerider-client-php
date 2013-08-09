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

	protected $typeInteger= array(
		Validator::TYPES => array(Validator::TYPE_INT)
	);

	protected $typePositiveInteger = array(
		Validator::TYPES => array(
			Validator::TYPE => Validator::TYPE_INT,
			Validator::MODIFIERS => array(Validator::MODIFIER_POSITIVE)
		)
	);

	protected $typeNegativeInteger = array(
		Validator::TYPES => array(
			Validator::TYPE => Validator::TYPE_INT,
			Validator::MODIFIERS => array(Validator::MODIFIER_NEGATIVE)
		)
	);

	protected $typeArray = array(
		Validator::TYPES => array(Validator::TYPE_ARRAY)
	);

	protected $typeArrayOfPositiveInts = array(
		Validator::TYPES => array(
			Validator::TYPE => Validator::TYPE_ARRAY,
			Validator::MODIFIERS => array(
				Validator::MODIFIER => Validator::MODIFIER_NESTED,
				Validator::MODIFIER_VALIDATORS => array(
					Validator::TYPES => array(
						Validator::TYPE => Validator::TYPE_INT,
						Validator::MODIFIERS => array(Validator::MODIFIER_POSITIVE)
					)
				)
			)
		)
	);

	protected $modifierIp = array(
		Validator::MODIFIERS => array(Validator::MODIFIER_IP)
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

	public function testNativeTypes()
	{
		// INTEGER

		$value = 'not an integer';
		$this->assertFalse(Validator::validate($value, $this->typeInteger));

		$value = 1.5;
		$this->assertFalse(Validator::validate($value, $this->typeInteger));

		$value = 1;
		$this->assertTrue(Validator::validate($value, $this->typeInteger));

		$value = -1;
		$this->assertFalse(Validator::validate($value, $this->typePositiveInteger));

		$value = 1;
		$this->assertTrue(Validator::validate($value, $this->typePositiveInteger));

		$value = 1;
		$this->assertFalse(Validator::validate($value, $this->typeNegativeInteger));

		$value = -1;
		$this->assertTrue(Validator::validate($value, $this->typeNegativeInteger));


		// ARRAY

		$value = 'not an array';
		$this->assertFalse(Validator::validate($value, $this->typeArray));

		$value = array();
		$this->assertTrue(Validator::validate($value, $this->typeArray));

		$value = 'not an array';
		$this->assertFalse(Validator::validate($value, $this->typeArray));

		$value = array();
		$this->assertTrue(Validator::validate($value, $this->typeArray));


		// ARRAY OF INTS

		$value = array('not an integer');
		$this->assertFalse(Validator::validate($value, $this->typeArrayOfPositiveInts));

		$value = array(1);
		$this->assertTrue(Validator::validate($value, $this->typeArrayOfPositiveInts));
	}

	public function testDataModifiers()
	{
		// IP

		$value = 'not an ip';
		$this->assertFalse(Validator::validate($value, $this->modifierIp));

		$value = '127.0000'; // Mallformed ip
		$this->assertFalse(Validator::validate($value, $this->modifierIp));

		$value = '127.0.0.1';
		$this->assertTrue(Validator::validate($value, $this->modifierIp));
	}
}