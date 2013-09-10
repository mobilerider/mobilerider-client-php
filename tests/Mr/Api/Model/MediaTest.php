<?php

namespace MrTest\Api\Http;

use Mr\Api\Model\Media;
use Mr\Api\Repository\MediaRepository;
use Mr\Api\Http\Client;


class MediaTest extends \PHPUnit_Framework_TestCase 
{
    protected $client = null;
    protected $repo = null;

    public function setUp() {
        $this->client = new Client(APP_HOST, APP_ID, APP_SECRET);
        $this->repo = new MediaRepository($this->client);
    }

    protected function getDummyMedia($data = NULL) {
        if (empty($data)) {
            $data = array('title'=>'some_name');
        } else {
            if (isset($data['type']) && Media::TYPE_LIVE == $data['type']) {
                // Adds default live stream data
                if (!array_key_exists('encoderPrimaryIp', $data)) {
                    $data['encoderPrimaryIp'] = '120.0.0.1';
                }

                if (!array_key_exists('encoderBackupIp', $data)) {
                    $data['encoderBackupIp'] = '120.0.0.1';
                }

                if (!array_key_exists('encoderPassword', $data)) {
                    $data['encoderPassword'] = 'test';
                }

                if (!array_key_exists('bitrates', $data)) {
                    $data['bitrates'] = array(1000);
                }
            }
        }

        // return new Media($this->repo, $data);
        return $this->repo->create($data);
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

    public function testSaveModel() {
        $media = $this->getDummyMedia(array(
            'title' => 'my pretty name!',
            'description' => 'some necessary description...',
            'type' => 'Videos',
            'file' => 'some_url?'
        ));
        $media->save();
        $this->assertNotNull($media->getId());
        $id = $media->getId();

        $media_count = count($this->repo->getAll());
        $this->assertGreaterThan(0, $media_count);

        $media2 = $this->repo->get($id);

        $this->assertNotNull($media2);

        // Compares expecting equqlity only fields setted in the client
        $this->assertEquals($media->title, $media2->title);        
        $this->assertEquals($media->description, $media2->description);
        $this->assertEquals($media->type, $media2->type);

        $this->repo->delete($media);

        $this->assertCount($media_count - 1, $this->repo->getAll());

        try {
            $this->repo->get($id); // Expecting Server error with status: Unknown media
        } catch (\Exception $expected) {
            if (!is_a($expected, 'Mr\Exception\ServerErrorException'))
                $this->fail('Failed to raise a ServerErrorException, got an ' . get_class($expected) . ' instead.');
        }
    }

    public function testGetSetTitleValue() {
        $media = $this->getDummyMedia(array(
            'title' => 'my pretty test name!',
            'description' => 'some necessary description...',
            'type' => 'Videos',
            'file' => 'some_url?'
        ));
        $this->assertTrue($media->isNew());

        $media->title = 'some_key_value';
        $this->assertEquals('some_key_value', $media->title);

        $this->assertTrue($media->isModified());
        $media->save();
        $this->assertFalse($media->isNew());
        $this->assertFalse($media->isModified());

        $media_id = $media->getId();

        $media = $this->repo->get($media_id);
        $this->assertNotNull($media);
        $this->assertEquals('some_key_value', $media->title);

        $media->delete();
        try {
            $this->repo->get($media_id); // Expecting Server error with status: Unknown media
        } catch (\Exception $expected) {
            if (!is_a($expected, 'Mr\Exception\ServerErrorException'))
                $this->fail('Failed to raise a ServerErrorException, got an ' . get_class($expected) . ' instead.');
        }
    }

    public function testGetSetDescriptionValue() {
        $original_name = 'my pretty test name!';
        $original_description = 'my pretty test description!';
        $new_description = 'my prettier test description!';

        $media = $this->getDummyMedia(array(
            'title' => $original_name,
            'description' => $original_description,
            'type' => 'Videos',
            'file' => 'some_url?'
        ));
        $this->assertTrue($media->isNew());
        $this->assertFalse($media->isModified());
        $media->save();
        $this->assertFalse($media->isNew());
        $this->assertFalse($media->isModified());

        $media_id = $media->getId();

        $media = $this->repo->get($media_id);
        $this->assertNotNull($media);
        $this->assertEquals($original_description, $media->description);

        $this->assertEquals($original_name, $media->title);
        $media->description = $new_description;
        $this->assertFalse($media->isNew());
        $this->assertTrue($media->isModified());

        // try {
            $media->save();
        // } catch (\Exception $e) {
        //     throw new \Exception(var_export($e, true));
        // }

        $this->assertFalse($media->isNew());
        $this->assertFalse($media->isModified());

        $media = $this->repo->get($media_id);
        $this->assertNotNull($media);
        $this->assertEquals($new_description, $media->description);

        $media->delete();
        try {
            $this->repo->get($media_id); // Expecting Server error with status: Unknown media
            $this->fail('Failed to raise a ServerErrorException, none exception was thrown');
        } catch (\Exception $expected) {
            if (!is_a($expected, 'Mr\Exception\ServerErrorException'))
                $this->fail('Failed to raise a ServerErrorException, got an ' . get_class($expected) . ' instead.');
        }
    }

    public function testIgnoredUnknownFields() {
        $media = $this->getDummyMedia(array(
            'title' => 'some nice name here',
            'description' => 'some necessary description...',
            'type' => 'Videos',
            'file' => 'some_url?',
            'an_unknown_field_name' => 'a_value_for_the_unknown_field'
        ));
        $this->assertTrue($media->isNew());
        $this->assertFalse($media->isModified());

        // Api ignores unknown fields so this can not throw any exception
        $media->save();
    }

    /*public function testExceptionForURLField() {
        $media = $this->getDummyMedia(array(
            'title' => 'some nice name here',
            'description' => 'some necessary description...',
            'type' => 'Videos',
            'file' => 'some_url?',
            'url' => 'a_value_for_the_url_field'
        ));
        $this->assertTrue($media->isNew());
        $this->assertFalse($media->isModified());
        $media->save();
    }*/

    public function testAsString() {
        $media = $this->getDummyMedia(array('id'=>1, 'title'=>'some_name'));
        $this->assertEquals('some_name', $media . '');
    }

    public function testICanNotSetMyOwnId() {
        $my_own_id = 99999998;

        try {
            $this->repo->get($my_own_id);
        } catch (\Exception $expected) {
            if (!is_a($expected, 'Mr\Exception\ServerErrorException'))
                $this->fail('Failed to raise a ServerErrorException, got an ' . get_class($expected) . ' instead.');
        }

        $media = $this->getDummyMedia(array(
            'id' => $my_own_id,
            'title' => 'my pretty test name with a wrong ID!'
        ));

        $this->assertFalse($media->isNew());
        $this->assertFalse($media->isModified());

        try {
            $media->save(); // Unknown media server exception
            $this->fail('Failed to raise a ServerErrorException, none exception was thrown');
        } catch (\Exception $expected) { 
            if (!is_a($expected, 'Mr\Exception\ServerErrorException'))
                $this->fail('Failed to raise a ServerErrorException, got an ' . get_class($expected) . ' instead');
        }
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

        $media->id = 1;
        $media->title = 'some_title_value';
        $media->description = 'some_description_value';
        $this->assertEquals('some_title_value', $media->title);
        $this->assertEquals('some_description_value', $media->description);
        $this->assertEquals('id', $media->getKeyField());
        $this->assertEquals(1, $media->id);

        $this->assertTrue($media->isModified());
        $media->saved();
        $this->assertFalse($media->isModified());

        $media->setData(array(
            'id' => 2,
            'title' => 'some_title_value_2',
            'description' => 'some_description_value_2'
        ));

        $this->assertTrue($media->isModified());
        $this->assertEquals(2, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_2', $media->title);
        $this->assertEquals('some_description_value_2', $media->description);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->title = 'some_title_value_3';
        $whatever->description = 'some_description_value_3';

        $media->setData($whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(3, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_3', $media->title);
        $this->assertEquals('some_description_value_3', $media->description);

        $apiobj_whatever = $this->getDummyMedia(array(
            'id' => 4,
            'title' => 'some_title_value_4',
            'description' => 'some_description_value_4'
        ));
        $media->setData($apiobj_whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(4, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_4', $media->title);
        $this->assertEquals('some_description_value_4', $media->description);
    }

    public function testUpdateData() {
        $media = $this->getDummyMedia();

        $media->{$media->getKeyField()} = 1;
        $media->title = 'some_title_value_1';
        $media->description = 'some_description_value_1';
        $this->assertEquals('some_title_value_1', $media->title);
        $this->assertEquals('some_description_value_1', $media->description);
        $this->assertEquals('id', $media->getKeyField());
        $this->assertEquals(1, $media->id);

        $this->assertTrue($media->isModified());
        $media->saved();
        $this->assertFalse($media->isModified());

        $media->updateData(array(
            'id' => 2,
            'title' => 'some_title_value_2',
            'description' => 'some_description_value_2'
        ));

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_2', $media->title);
        $this->assertEquals('some_description_value_2', $media->description);

        $whatever = new \stdClass();
        $whatever->id = 3;
        $whatever->title = 'some_title_value_3';
        $whatever->description = 'some_description_value_3';

        $media->updateData($whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_3', $media->title);
        $this->assertEquals('some_description_value_3', $media->description);

        $apiobj_whatever = $this->getDummyMedia(array(
            'id' => 4,
            'title' => 'some_title_value_4',
            'description' => 'some_description_value_4'
        ));
        $media->updateData($apiobj_whatever);

        $this->assertTrue($media->isModified());
        $this->assertEquals(1, $media->{$media->getKeyField()});
        $this->assertEquals('some_title_value_4', $media->title);
        $this->assertEquals('some_description_value_4', $media->description);
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testEmptyLivePrimaryIpField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->encoderPrimaryIp = null;
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testInvalidLivePrimaryIpField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));
        
        $media->encoderPrimaryIp = 'Mallformed.Ip';
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testEmptyLiveBackupIpField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->encoderBackupIp = null;
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testInvalidLiveBackupIpField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->encoderBackupIp = 'Mallformed.Ip';
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testEmptyLivePasswordField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->encoderPassword = null;
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testEmptyLiveBitratesField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->bitrates = null;
        $media->save();
    }

    /**
     * @expectedException Mr\Exception\InvalidFieldException
     */
    public function testInvalidLiveBitratesField() 
    {
        $media = $this->getDummyMedia(array(
            'type' => Media::TYPE_LIVE
        ));

        $media->bitrates = array('not an int');
        $media->save();
    }

    public function testCreateLiveMedia() 
    {
        $media = $this->getDummyMedia(array(
            'title' => 'Live Media Creation Test',
            'type' => Media::TYPE_LIVE,
            'description' => 'Test live media from client',
            'DescriptionSmall' => 'tag1, tag2',
            'encoderPrimaryIp' => '127.0.0.1',
            'encoderBackupIp' => '127.0.0.1',
            'encoderPassword' => 'test',
            'bitrates' => array(696, 1096, 2096)
        ));

        $media->save();

        // Removes test media
        $media->delete();
    }

    public function testInvalidReturnedLiveMedia() 
    {
        $dataOrig = array(
            'title' => 'Live Media Creation Test',
            'type' => Media::TYPE_LIVE,
            'description' => 'Test live media from client',
            'DescriptionSmall' => 'tag1, tag2',
            'encoderPrimaryIp' => '127.0.0.1',
            'encoderBackupIp' => '127.0.0.1',
            'encoderPassword' => 'test',
            'bitrates' => array(696, 1096, 2096)
        );

        $media = $this->getDummyMedia($dataOrig);

        $dataOrig['id'] = 99999;

        $streamOrig = array(
            'encoderPrimaryIp' => '127.0.0.1',
            'encoderBackupIp' => '127.0.0.1',
            'encoderPassword' => 'test',
            'encoderUsername' => 'user',
            'entrypoints' => array(
                'Primary' => 'some.host',
                'Backup' => 'some.host'
            )
        );

        foreach ($streamOrig as $key => $value) {
            $stream = $streamOrig;
            $stream[$key] = '';
            
            $data = $dataOrig;
            $data['stream'] = $stream;
            $data = json_decode(json_encode($data));
            
            try {
                $media->saved($data);
            } catch (\Exception $ex) {
                $this->assertInstanceOf('Mr\Exception\InvalidFieldException', $ex);
                $this->assertEquals('Invalid field: stream. The value ' . var_export($media->stream, true) . ' contains invalid values', $ex->getMessage());
            } 
        }
    }

    public function testReturnedStreamFieldsInLiveMedia() 
    {
        $data = array(
            'title' => 'Live Media Creation Test',
            'type' => Media::TYPE_LIVE,
            'description' => 'Test live media from client',
            'DescriptionSmall' => 'tag1, tag2',
            'encoderPrimaryIp' => '127.0.0.1',
            'encoderBackupIp' => '127.0.0.1',
            'encoderPassword' => 'test',
            'bitrates' => array(696, 1096, 2096)
        );

        $media = $this->getDummyMedia($data);

        $stream = array(
            'encoderPrimaryIp' => '127.0.0.1',
            'encoderBackupIp' => '127.0.0.1',
            'encoderPassword' => 'test',
            'encoderUsername' => 'user',
            'entrypoints' => array(
                'Primary' => 'some.host',
                'Backup' => 'some.host'
            )
        );

        $data['id'] = 99999;
        $data['stream'] = $stream;
        $data = json_decode(json_encode($data));

        $media->saved($data);
    }
}
