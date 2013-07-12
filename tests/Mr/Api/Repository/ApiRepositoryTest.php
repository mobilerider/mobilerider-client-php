<?php 

namespace MrTest\Api\Repository;

use Mr\Api\Repository\ApiRepository;
use Mr\Api\AbstractClient;
use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\Http\Adapter\MockAdapter;
use Mr\Api\Model\Channel;
use Mr\Api\Repository\ChannelRepository;

class ApiRepositoryTest extends \PHPUnit_Framework_TestCase
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

    public function testGetAllMetadata()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->channelsData));

        $repo = new ChannelRepository($this->_client);
        $metadata = array();
        $channels = $repo->getAll(null, $metadata, false);

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

    public function testGetAllChannels()
    {
        // Creating response with specific url
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->channelsData));

        $repo = new ChannelRepository($this->_client);
        $metadata = array();
        $channels = $repo->getAll(null, $metadata, false);

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

        if ($channel) {
            // Check return item type
            \PHPUnit_Framework_Assert::assertInstanceOf(self::MODEL_NAMESPACE . 'Channel', $channel);
        }

        // Validating fields data
        $this->validatesObjectsData(array($this->channelData['object']), $channel);
    }
}
