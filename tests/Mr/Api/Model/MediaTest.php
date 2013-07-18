<?php

namespace MrTest\Api\Http;

use Mr\Api\Model\Media;


class MediaTest extends \PHPUnit_Framework_TestCase {

    protected function getDummyMedia($data = NULL) {
        if (empty($data)) {
            $data = array('title'=>'some_name');
        }
        return new Media('Media', $data);
    }

    public function testGetModel() {
        $media = $this->getDummyMedia();
        $this->assertEquals('Media', $media->getModel());
    }

    public function testGetStdFieldNames() {
        $media = $this->getDummyMedia();
        $this->assertEquals('title', $media->getStringField());
        $this->assertEquals('id', $media->getKeyField());
    }

    public function testIsNew() {
        $media = $this->getDummyMedia();
        $this->assertTrue($media->isNew());

        $media = $this->getDummyMedia(array('id'=>1));
        $this->assertFalse($media->isNew());
    }

    public function testGetDataFieldValues() {
        $media = $this->getDummyMedia();

        $media->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $media->some_key);

        $this->assertTrue($media->isModified());
        $media->saved();
        $this->assertFalse($media->isModified());

        $media->setData(array(
            'some_key' => 'other_key_value',
            'some_other_key' => 'yet_another_key_value'
        ));

        $this->assertTrue($media->isModified());
        $this->assertEquals('other_key_value', $media->some_key);
        $this->assertEquals('yet_another_key_value', $media->some_other_key);

        $media->saved();
        $this->assertFalse($media->isModified());

        $this->assertTrue(isset($media->some_key));
        $this->assertFalse(isset($media->non_existent_key));
    }

    public function testAsString() {
        $media = $this->getDummyMedia(array('id'=>1, 'title'=>'some_name'));
        $this->assertEquals('some_name', $media . '');
    }

    /**
     * @expectedException Mr\Exception\InvalidRepositoryException
     */
    public function testDeletedDelete() {
        $media = $this->getDummyMedia(array('id'=>1, 'title'=>'some_name'));
        $this->assertFalse($media->isNew());
        $media->deleted();
        $this->assertTrue($media->isNew());
        $this->assertFalse($media->isModified());
        $media->delete();
    }

    /**
     * @expectedException Mr\Exception\InvalidRepositoryException
     */
    public function testDeletedSave() {
        $media = $this->getDummyMedia(array('id'=>1, 'title'=>'some_name'));
        $this->assertFalse($media->isNew());
        $media->deleted();
        $this->assertTrue($media->isNew());
        $this->assertFalse($media->isModified());
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFormatException
     */
    public function testSetDataException() {
        $media = $this->getDummyMedia();
        $media->setData('some data?');
    }

    /**
     * @expectedException Mr\Exception\InvalidFormatException
     */
    public function testUpdateDataException() {
        $media = $this->getDummyMedia();
        $media->updateData('some data?');
    }

    public function testSetData() {
        $media = $this->getDummyMedia();

        $media->{$media->getKeyField()} = 1;
        $media->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $media->some_key);
        $this->assertEquals('id', $media->getKeyField());
        $this->assertEquals(1, $media->id);

        $this->assertTrue($media->isModified());
        $media->saved();
        $this->assertFalse($media->isModified());

        $media->setData(array(
            'id' => 2,
            'some_key' => 'other_key_value_from_array',
            'some_other_key' => 'yet_another_key_value_from_array'
        ));

        $this->assertTrue($media->isModified());
        $this->assertEquals(2, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_array', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_array', $media->some_other_key);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->some_key = 'other_key_value_from_object';
        $whatever->some_other_key = 'yet_another_key_value_from_object';

        $media->setData($whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(3, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_object', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_object', $media->some_other_key);

        $apiobj_whatever = $this->getDummyMedia(array(
            'id' => 4,
            'some_key' => 'other_key_value_from_apiobject',
            'some_other_key' => 'yet_another_key_value_from_apiobject'
        ));
        $media->setData($apiobj_whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(4, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_apiobject', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_apiobject', $media->some_other_key);
    }

    public function testUpdateData() {
        $media = $this->getDummyMedia();

        $media->{$media->getKeyField()} = 1;
        $media->some_key = 'some_key_value';
        $this->assertEquals('some_key_value', $media->some_key);
        $this->assertEquals('id', $media->getKeyField());
        $this->assertEquals(1, $media->id);

        $this->assertTrue($media->isModified());
        $media->saved();
        $this->assertFalse($media->isModified());

        $media->updateData(array(
            'id' => 2,
            'some_key' => 'other_key_value_from_array',
            'some_other_key' => 'yet_another_key_value_from_array'
        ));

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_array', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_array', $media->some_other_key);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->some_key = 'other_key_value_from_object';
        $whatever->some_other_key = 'yet_another_key_value_from_object';

        $media->updateData($whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_object', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_object', $media->some_other_key);

        $apiobj_whatever = $this->getDummyMedia(array(
            'id' => 4,
            'some_key' => 'other_key_value_from_apiobject',
            'some_other_key' => 'yet_another_key_value_from_apiobject'
        ));
        $media->updateData($apiobj_whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('other_key_value_from_apiobject', $media->some_key);
        $this->assertEquals('yet_another_key_value_from_apiobject', $media->some_other_key);
    }
}
