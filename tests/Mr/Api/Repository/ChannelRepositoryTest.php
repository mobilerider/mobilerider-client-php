<?php

namespace MrTest\Api\Repository;

use Mr\Api\Repository\ApiRepository;
use Mr\Api\AbstractClient;
use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\Http\Adapter\MockAdapter;
use Mr\Api\Model\Channel;
use Mr\Api\Repository\ChannelRepository;


/*
 *  Channel subclass to test if hook methods are called
 */
class SubclassedChannel extends Channel {
    protected $_before_save_called = false;
    protected $_after_save_called = false;
    protected $_before_delete_called = false;
    protected $_after_delete_called = false;

    protected function resetTestState() {
        $this->_before_save_called = false;
        $this->_after_save_called = false;
        $this->_before_delete_called = false;
        $this->_after_delete_called = false;
    }

    public function wasBeforeSaveCalled() {
        return $this->_before_save_called;
    }

    public function wasAfterSaveCalled() {
        return $this->_after_save_called;
    }

    public function wasBeforeDeleteCalled() {
        return $this->_before_delete_called;
    }

    public function wasAfterDeleteCalled() {
        return $this->_after_delete_called;
    }

    public function beforeSave() {
        $this->_before_save_called = true;
        return parent::beforeSave();
    }

    public function afterSave() {
        $this->_after_save_called = true;
        return parent::afterSave();
    }

    public function beforeDelete() {
        $this->_before_delete_called = true;
        return parent::beforeDelete();
    }

    public function afterDelete() {
        $this->_after_delete_called = true;
        return parent::afterDelete();
    }
}

class SubclassedChannelRepository extends ChannelRepository {

    /**
    * Returns current model name, eg: Media
    *
    * @return string
    */
    public function getModel()
    {
        // preg_match("/([^\\\]+$)/", get_called_class(), $matches);
        // return str_replace('Repository', '', $matches[0]);
        return parent::getModel();
    }

    public function create($data = null)
    {
        // $modelClass = self::MODEL_NAMESPACE . $this->getModel();
        return new SubclassedChannel($this, $data);
    }
}

class ChannelRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const HOST = 'http://test.host.com';
    const USERNAME = 'user';
    const PASSWORD = 'pass';
    const MODEL_NAMESPACE = 'Mr\\Api\\Model\\';
    const COLLECTION_NAMESPACE = 'Mr\\Api\\Collection\\';

    protected $channelData = array(
        'status' => ApiRepository::STATUS_OK,
        'object' => array(
            'id' => 1,
            'url' => 'http://site.channel.com',
            'name' => 'Channel 1'
        )
    );
    protected $channelsData = array(
        'status' => ApiRepository::STATUS_OK,
        'meta' => array(
            'total' => 3,
            'page' => 1,
            'pages' => 1,
            'limit' => 3
        ),
        'objects' => array(
             array(
                'id' => 1,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 1'
            ),
            array(
                'id' => 2,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 2'
            ),
            array(
                'id' => 3,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 3'
            )
        )
    );

    protected $_client;
    protected $_clientMockAdapter;

    public function setUp()
    {
        $this->_client = new Client(self::HOST, self::USERNAME, self::PASSWORD);
        $this->_clientMockAdapter = new MockAdapter();
        $this->_client->setAdapter($this->_clientMockAdapter);
    }

    protected function validatesObjectsData($dataObjects, $returnedModelObjects)
    {
        $dataObjects = is_array($dataObjects) ? $dataObjects : array($dataObjects);
        $returnedModelObjects = is_array($returnedModelObjects) ? $returnedModelObjects : array($returnedModelObjects);

        foreach ($dataObjects as $index => $data) {
            \PHPUnit_Framework_Assert::assertArrayHasKey($index, $returnedModelObjects);

            if (array_key_exists($index, $returnedModelObjects)) { 
                $object = $returnedModelObjects[$index];

                foreach ($data as $key => $value) {
                    // Check property existance
                    \PHPUnit_Framework_Assert::assertArrayHasKey($key, $object->getData());
                    // Check for same value
                    \PHPUnit_Framework_Assert::assertEquals($value, $object->{$key});
                }
            }
        }
    }

    public function testGetAllChannels()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->channelsData));

        $repo = new ChannelRepository($this->_client);
        $metadata = array();
        $channels = $repo->getAllRecords(null, $metadata);

        // Checking response (validating specific url was matched)
        \PHPUnit_Framework_Assert::assertTrue($this->_client->getResponse()->isOK());
        // Check return type
        \PHPUnit_Framework_Assert::assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $channels);
        // Check return count
        \PHPUnit_Framework_Assert::assertEquals(count($this->channelsData['objects']), count($channels));

        if ($channels) {
            // Check return item type
            \PHPUnit_Framework_Assert::assertInstanceOf(self::MODEL_NAMESPACE . 'Channel', $channels[0]);
        }

        // Validating fields data
        $this->validatesObjectsData($this->channelsData['objects'], $channels);
    }

    public function testGetOneChannel()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', json_encode($this->channelData));

        $repo = new ChannelRepository($this->_client);
        $channel = $repo->get($this->channelData['object']['id']);

        // Check object returned is not null
        \PHPUnit_Framework_Assert::assertNotNull($channel);

        // Check return item type
        \PHPUnit_Framework_Assert::assertInstanceOf(self::MODEL_NAMESPACE . 'Channel', $channel);

        // Validating fields data
        $this->validatesObjectsData(array($this->channelData['object']), $channel);
    }

    // TODO: This should be a dedicated exception for when an object is not found,
    //       like "Mr\Exception\EntityNotFoundException"
    /**
     * @expectedException Mr\Exception\ServerErrorException
     */
    public function testGetOneChannelNotFound() {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(
            Response::STATUS_OK,
            'api/channel/1',
            json_encode(array('status' => 'Unknown Channel')));

        $repo = new ChannelRepository($this->_client);

        $this->assertNull($repo->get($this->channelData['object']['id']));
    }

    /**
     * @expectedException Mr\Exception\DeniedEntityAccessException
     */
    public function testGetOneChannelAccessDenied()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(
            Response::STATUS_OK,
            'api/channel/1',
            json_encode(array('status' => 'Access denied')));

        $repo = new ChannelRepository($this->_client);

        $this->assertNull($repo->get($this->channelData['object']['id']));
    }

    public function testDeleteOneChannelAndBeforeAfterHooksAreCalled() {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', json_encode($this->channelData));

        $repo = new SubclassedChannelRepository($this->_client);
        $channel = $repo->get($this->channelData['object']['id']);
        $this->assertNotNull($channel);
        $this->assertFalse($channel->isNew());
        $this->assertInstanceOf('Mr\Api\Model\Channel', $channel);

        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', '{"status": "ok"}');
        $this->assertFalse($channel->wasBeforeDeleteCalled());
        $this->assertFalse($channel->wasAfterDeleteCalled());
        $channel->delete();
        $this->assertTrue($channel->wasBeforeDeleteCalled());
        $this->assertTrue($channel->wasAfterDeleteCalled());
        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());
    }

    public function testSaveOneChannelAndBeforeAfterHooksAreCalled() {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', json_encode($this->channelData));

        $repo = new SubclassedChannelRepository($this->_client);
        $channel = $repo->get($this->channelData['object']['id']);
        $this->assertNotNull($channel);
        $this->assertFalse($channel->isNew());
        $this->assertInstanceOf('Mr\Api\Model\Channel', $channel);

        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', json_encode($this->channelData));
        $this->assertFalse($channel->wasBeforeSaveCalled());
        $this->assertFalse($channel->wasAfterSaveCalled());
        $channel->save();
        $this->assertTrue($channel->wasBeforeSaveCalled());
        $this->assertTrue($channel->wasAfterSaveCalled());

        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());

        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel/1', json_encode($this->channelData));
        $channel = $repo->create(array('name'=> 'some_name'));

        $this->assertTrue($channel->isNew());
        $this->assertFalse($channel->isModified());

        $this->assertFalse($channel->wasBeforeSaveCalled());
        $this->assertFalse($channel->wasAfterSaveCalled());
        $channel->save();
        $this->assertTrue($channel->wasBeforeSaveCalled());
        $this->assertTrue($channel->wasAfterSaveCalled());

        $this->assertFalse($channel->isNew());
        $this->assertFalse($channel->isModified());
    }

    public function testGetAllMetadata()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->channelsData));

        $repo = new ChannelRepository($this->_client);
        $metadata = array();
        $channels = $repo->getAllRecords(null, $metadata);

        \PHPUnit_Framework_Assert::assertEquals($this->channelsData['meta'], $metadata);
    }

    public function testCollectionReturnType()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->channelsData));

        $repo = new ChannelRepository($this->_client);
        $channels = $repo->getAll();

        \PHPUnit_Framework_Assert::assertInstanceOf(self::COLLECTION_NAMESPACE . 'ApiObjectCollection', $channels);
        // At this points none server request should has done due to the lazy behavior
        $this->assertNull($this->_client->getRequest());
        $this->assertNull($this->_client->getResponse());
    }
}
