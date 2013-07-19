<?php

namespace MrTest\Api\Http;

use Mr\Api\Model\Channel;
use Mr\Api\Repository\ChannelRepository;
use Mr\Api\Http\Client;


class ChannelTest extends \PHPUnit_Framework_TestCase {

    const HOST = 'api.devmobilerider.com';
    const APP_ID = '7af9ca9a0eba0662d5a494a36c0af12a';
    const APP_SECRET = 'f4b6833ac8ce175bd4f5e9a81214a5c20f3aef7680ba64720e514d94102abe39';

    protected $client = null;
    protected $repo = null;

    public function setUp() {
        $this->client = new Client(self::HOST, self::APP_ID, self::APP_SECRET);
        $this->repo = new ChannelRepository($this->client);
    }

    protected function getDummyChannel($data = NULL) {
        if (empty($data)) {
            $data = array('name'=>'some_name');
        }
        // return new Channel($this->repo, $data);
        return $this->repo->create($data);
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

    public function testSaveModel() {
        $channel = $this->getDummyChannel(array('name' => 'my pretty name!'));
        $channel->save();
        $this->assertNotNull($channel->getId());
        $id = $channel->getId();

        $channel_count = count($this->repo->getAll());
        $this->assertGreaterThan(0, $channel_count);

        $channel2 = $this->repo->get($id);

        $this->assertNotNull($channel2);
        $this->assertEquals($channel, $channel2);

        $this->repo->delete($channel);

        $this->assertCount($channel_count - 1, $this->repo->getAll());
        $this->assertNull($this->repo->get($id));
    }

    public function testGetSetNameValue() {
        $channel = $this->getDummyChannel(array(
            'name' => 'my pretty test name!'
        ));
        $this->assertTrue($channel->isNew());

        $channel->name = 'some_key_value';
        $this->assertEquals('some_key_value', $channel->name);

        $this->assertTrue($channel->isModified());
        $channel->save();
        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());

        $channel_id = $channel->getId();

        $channel = $this->repo->get($channel_id);
        $this->assertNotNull($channel);
        $this->assertEquals('some_key_value', $channel->name);

        $channel->delete();
        $this->assertNull($this->repo->get($channel_id));
    }

    public function testGetSetDescriptionValue() {
        $original_name = 'my pretty test name!';
        $original_description = 'my pretty test description!';
        $new_description = 'my prettier test description!';

        $channel = $this->getDummyChannel(array(
            'name' => $original_name,
            'description' => $original_description
        ));
        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());
        $channel->save();
        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());

        $channel_id = $channel->getId();

        $channel = $this->repo->get($channel_id);
        $this->assertNotNull($channel);
        $this->assertEquals($original_description, $channel->description);

        $channel->description = $new_description;
        $this->assertFalse($channel->isNew());
        $this->assertTrue($channel->isModified());
        $channel->save();
        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());

        $channel = $this->repo->get($channel_id);
        $this->assertNotNull($channel);
        $this->assertEquals($new_description, $channel->description);

        $channel->delete();
        $this->assertNull($this->repo->get($channel_id));
    }

    // TODO: This could change to a more meaningful/specific exception class
    /**
     * @expectedException Mr\Exception\ServerErrorException
     */
    public function testExceptionForUnknownFields() {
        $channel = $this->getDummyChannel(array(
            'name' => 'some nice name here',
            'an_unknown_field_name' => 'a_value_for_the_unknown_field'
        ));
        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());
        $channel->save();
    }

    public function testAsString() {
        $channel = $this->getDummyChannel(array('id'=>1, 'name'=>'some_name'));
        $this->assertEquals('some_name', $channel . '');
    }

    public function testICanNotSetMyOwnId() {
        $my_own_id = 99999999;
        $channel = $this->getDummyChannel(array(
            'id' => $my_own_id,
            'name' => 'my pretty test name!'
        ));

        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());
        $channel->save();
        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());

        $channel_id = $channel->getId();
        $this->assertNotEquals($my_own_id, $channel_id);

        $channel = $this->repo->get($channel_id);
        $this->assertNotNull($channel);

        $channel->delete();
        $this->assertNull($this->repo->get($channel_id));
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
