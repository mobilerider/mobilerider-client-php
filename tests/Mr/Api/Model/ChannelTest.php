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

    /**
     * @expectedException Mr\Exception\InvalidRepositoryException
     */
    public function testDeletedDelete() {
        $channel = $this->getDummyChannel(array('id'=>1, 'name'=>'some_name'));
        $this->assertFalse($channel->isNew());
        $channel->deleted();
        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());
        $channel->delete();
    }

    /**
     * @expectedException Mr\Exception\InvalidRepositoryException
     */
    public function testDeletedSave() {
        $channel = $this->getDummyChannel(array('id'=>1, 'name'=>'some_name'));
        $this->assertFalse($channel->isNew());
        $channel->deleted();
        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());
        $channel->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFormatException
     */
    public function testSetDataException() {
        $channel = $this->getDummyChannel();
        $channel->setData('some data?');
    }

    /**
     * @expectedException Mr\Exception\InvalidFormatException
     */
    public function testUpdateDataException() {
        $channel = $this->getDummyChannel();
        $channel->updateData('some data?');
    }

    public function testSetData() {
        $channel = $this->getDummyChannel();

        $channel->{$channel->getKeyField()} = 1;
        $channel->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $channel->some_key);
        $this->assertEquals('id', $channel->getKeyField());
        $this->assertEquals(1, $channel->id);

        $this->assertTrue($channel->isModified());
        $channel->saved();
        $this->assertFalse($channel->isModified());

        $channel->setData(array(
            'id' => 2,
            'some_key' => 'other_key_value_from_array',
            'some_other_key' => 'yet_another_key_value_from_array'
        ));

        $this->assertTrue($channel->isModified());
        $this->assertEquals(2, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_array', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_array', $channel->some_other_key);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->some_key = 'other_key_value_from_object';
        $whatever->some_other_key = 'yet_another_key_value_from_object';

        $channel->setData($whatever);

        $this->assertTrue($channel->isModified());
        $this->assertEquals(3, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_object', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_object', $channel->some_other_key);

        $apiobj_whatever = $this->getDummyChannel(array(
            'id' => 4,
            'some_key' => 'other_key_value_from_apiobject',
            'some_other_key' => 'yet_another_key_value_from_apiobject'
        ));
        $channel->setData($apiobj_whatever);

        $this->assertTrue($channel->isModified());
        $this->assertEquals(4, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_apiobject', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_apiobject', $channel->some_other_key);
    }

    public function testUpdateData() {
        $channel = $this->getDummyChannel();

        $channel->{$channel->getKeyField()} = 1;
        $channel->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $channel->some_key);
        $this->assertEquals('id', $channel->getKeyField());
        $this->assertEquals(1, $channel->id);

        $this->assertTrue($channel->isModified());
        $channel->saved();
        $this->assertFalse($channel->isModified());

        $channel->updateData(array(
            'id' => 2,
            'some_key' => 'other_key_value_from_array',
            'some_other_key' => 'yet_another_key_value_from_array'
        ));

        $this->assertTrue($channel->isModified());
        $this->assertEquals(1, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_array', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_array', $channel->some_other_key);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->some_key = 'other_key_value_from_object';
        $whatever->some_other_key = 'yet_another_key_value_from_object';

        $channel->updateData($whatever);

        $this->assertTrue($channel->isModified());
        $this->assertEquals(1, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_object', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_object', $channel->some_other_key);

        $apiobj_whatever = $this->getDummyChannel(array(
            'id' => 4,
            'some_key' => 'other_key_value_from_apiobject',
            'some_other_key' => 'yet_another_key_value_from_apiobject'
        ));
        $channel->updateData($apiobj_whatever);

        $this->assertTrue($channel->isModified());
        $this->assertEquals(1, $channel->{$channel->getKeyField()});
        $this->assertEquals('other_key_value_from_apiobject', $channel->some_key);
        $this->assertEquals('yet_another_key_value_from_apiobject', $channel->some_other_key);
    }
}
