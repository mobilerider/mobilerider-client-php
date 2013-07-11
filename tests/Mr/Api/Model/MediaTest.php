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
}
