<?php

namespace MrTest\Api\Util;

use Mr\Api\Util\CommonUtils;


class CommonUtilsTest extends \PHPUnit_Framework_TestCase {

    public function testDecodeJson() {
        $data = CommonUtils::decodeJson('{}');
        $this->assertTrue(is_object($data));

        $data = CommonUtils::decodeJson('{ "status": "ok" }');
        $this->assertTrue(is_object($data));
        $this->assertObjectHasAttribute('status', $data);

        $this->assertEquals('ok', $data->status);

        $data = CommonUtils::decodeJson('["one", "two", "three", "no more"]');
        $this->assertEquals(array('one', 'two', 'three', 'no more'), $data);
    }

    /**
     * @expectedException Mr\Exception\JsonException
     * @expectedExceptionMessage JSON Error: Syntax error, malformed JSON. JSON Data: foo?
     */
    public function testDecodeNotJson() {
        $data = CommonUtils::decodeJson('foo?');
    }

    /**
     * @expectedException Mr\Exception\JsonException
     * @expectedExceptionMessage JSON Error: Syntax error, malformed JSON. JSON Data: { "status": "ok", }
     */
    public function testDecodeMalformedJson() {
        $data = CommonUtils::decodeJson('{ "status": "ok", }');  // <-- has an extra comma
    }

    public function testEncodeJson() {
        $data = CommonUtils::encodeJson(array(
            'first_key' => 'first_value',
            'second-key' => 'second-value'
        ));
        $this->assertContains('first_key', $data);
        $this->assertContains('first_value', $data);
        $this->assertContains('second-key', $data);
        $this->assertContains('second-value', $data);
    }
}
