<?php

namespace MrTest\Api\Http;

use Mr\Api\Model\Channel;


class ChannelTest extends \PHPUnit_Framework_TestCase {

    protected function getDummyChannel($data = NULL) {
        if (empty($data)) {
            $data = array('name'=>'some_name');
        }
        return new Channel('Channel', $data);
    }

    public function testGetModel() {
        $channel = $this->getDummyChannel();
        $this->assertEquals('Channel', $channel->getModel());
    }

    public function testGetStdFieldNames() {
        $channel = $this->getDummyChannel();
        $this->assertEquals('name', $channel->getStringField());
        $this->assertEquals('id', $channel->getKeyField());
    }

    public function testIsNew() {
        $channel = $this->getDummyChannel();
        $this->assertTrue($channel->isNew());

        $channel = $this->getDummyChannel(array('id'=>1));
        $this->assertFalse($channel->isNew());
    }

    public function testGetDataFieldValues() {
        $channel = $this->getDummyChannel();

        $channel->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $channel->some_key);

        $this->assertTrue($channel->isModified());
        $channel->saved();
        $this->assertFalse($channel->isModified());

        $channel->setData(array(
            'some_key' => 'other_key_value',
            'some_other_key' => 'yet_another_key_value'
        ));

        $this->assertTrue($channel->isModified());
        $this->assertEquals('other_key_value', $channel->some_key);
        $this->assertEquals('yet_another_key_value', $channel->some_other_key);

        $channel->saved();
        $this->assertFalse($channel->isModified());

        $this->assertTrue(isset($channel->some_key));
        $this->assertFalse(isset($channel->non_existent_key));
    }

    public function testAsString() {
        $channel = $this->getDummyChannel(array('id'=>1, 'name'=>'some_name'));
        $this->assertEquals('some_name', $channel . '');
    }
}
