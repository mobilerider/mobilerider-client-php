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

	protected $modifierUrl = array(
		Validator::MODIFIERS => array(Validator::MODIFIER_URL)
	);

	public function testConstraints()
	{
		$value = null;
		list($valid, $message) = Validator::validate($value, $this->requiredValidator);
		$this->assertFalse($valid);

		$value = 'not empty';
		list($valid, $message) = Validator::validate($value, $this->requiredValidator);
		$this->assertTrue($valid);

		$value = null;
		list($valid, $message) = Validator::validate($value, $this->requiredNestedValidator);
		$this->assertFalse($valid);

		$value = 'not empty'; // Value must be an array if includes a nested validator
		list($valid, $message) = Validator::validate($value, $this->requiredNestedValidator);
		$this->assertFalse($valid);

		$value = array(); // Array must contains at least one item if nested validator is used
		list($valid, $message) = Validator::validate($value, $this->requiredNestedValidator);
		$this->assertFalse($valid);

		$value = array('not empty');
		list($valid, $message) = Validator::validate($value, $this->requiredNestedValidator);
		$this->assertTrue($valid);
	}

	public function testNativeTypes()
	{
		// INTEGER

		$value = 'not an integer';
		list($valid, $message) = Validator::validate($value, $this->typeInteger);
		$this->assertFalse($valid);

		$value = 1.5;
		list($valid, $message) = Validator::validate($value, $this->typeInteger);
		$this->assertFalse($valid);

		$value = 1;
		list($valid, $message) = Validator::validate($value, $this->typeInteger);
		$this->assertTrue($valid);

		$value = -1;
		list($valid, $message) = Validator::validate($value, $this->typePositiveInteger);
		$this->assertFalse($valid);

		$value = 1;
		list($valid, $message) = Validator::validate($value, $this->typePositiveInteger);
		$this->assertTrue($valid);

		$value = 1;
		list($valid, $message) = Validator::validate($value, $this->typeNegativeInteger);
		$this->assertFalse($valid);

		$value = -1;
		list($valid, $message) = Validator::validate($value, $this->typeNegativeInteger);
		$this->assertTrue($valid);


		// ARRAY

		$value = 'not an array';
		list($valid, $message) = Validator::validate($value, $this->typeArray);
		$this->assertFalse($valid);

		$value = array();
		list($valid, $message) = Validator::validate($value, $this->typeArray);
		$this->assertTrue($valid);

		$value = 'not an array';
		list($valid, $message) = Validator::validate($value, $this->typeArray);
		$this->assertFalse($valid);

		$value = array();
		list($valid, $message) = Validator::validate($value, $this->typeArray);
		$this->assertTrue($valid);


		// ARRAY OF INTS

		$value = array('not an integer');
		list($valid, $message) = Validator::validate($value, $this->typeArrayOfPositiveInts);
		$this->assertFalse($valid);

		$value = array(1);
		list($valid, $message) = Validator::validate($value, $this->typeArrayOfPositiveInts);
		$this->assertTrue($valid);
	}

	public function testDataModifiers()
	{
		// IP

		$value = 'not an ip';
		list($valid, $message) = Validator::validate($value, $this->modifierIp);
		$this->assertFalse($valid);

		$value = '127.0000'; // Mallformed ip
		list($valid, $message) = Validator::validate($value, $this->modifierIp);
		$this->assertFalse($valid);

		$value = '127.0.0.1';
		list($valid, $message) = Validator::validate($value, $this->modifierIp);
		$this->assertTrue($valid);


		// URL

		$value = 'not a valid url';
		list($valid, $message) = Validator::validate($value, $this->modifierUrl);
		$this->assertFalse($valid);

		$value = 'http://testing.com';
		list($valid, $message) = Validator::validate($value, $this->modifierUrl);
		$this->assertTrue($valid);
	}

	public function testMessages()
	{
        $this->assertEquals('The value 1 is empty', Validator::getMessage(1, Validator::CONSTRAINT_REQUIRED));
        $this->assertEquals('The value 1 is empty', Validator::getMessage(1, Validator::CONSTRAINT_NUMERIC_REQUIRED));
        $this->assertEquals('The value 1 is not a positive number', Validator::getMessage(1, Validator::MODIFIER_POSITIVE));
        $this->assertEquals('The value 1 is not a negative number', Validator::getMessage(1, Validator::MODIFIER_NEGATIVE));
        $this->assertEquals('The value 1 is not a valid Ip', Validator::getMessage(1, Validator::MODIFIER_IP));
        $this->assertEquals('The value 1 is not a valid Url', Validator::getMessage(1, Validator::MODIFIER_URL));
        $this->assertEquals('The value 1 contains invalid values', Validator::getMessage(1, Validator::MODIFIER_NESTED));
	}
}