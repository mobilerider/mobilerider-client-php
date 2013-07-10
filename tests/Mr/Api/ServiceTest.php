<?php

namespace MrTest\Api;

use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\AbstractClient;
use Mr\Api\Service;
use Mr\Api\Http\Adapter\MockAdapter;
use Mr\Api\Model\Channel;
use Mr\Api\Model\Media;

class ServiceTest extends \PHPUnit_Framework_TestCase {

    const APP_ID = 'some_app';
    const APP_SECRET = 'some_app_secret';

    protected $_service;
    protected $_mockService;

    public function setUp() {
        $this->_service = new Service(self::APP_ID, self::APP_SECRET);
    }

    protected function _mockTheServiceClient($content, $path='/test/path', $status=Response::STATUS_OK) {
        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy($status, $path, $content);
        // Add mock adapter
        $service_client = $this->_service->getClient();
        $service_client->setAdapter($mockAdapter);
    }

    public function testCreateChannelRepositoryObject() {
        // No need to mock adapters here, until we call `$obj->save()`
        $data = array('id'=> 1, 'name' => 'some_name');
        $obj = $this->_service->create('Channel', $data);
        $this->assertEquals($data, $obj->getData());
    }

    public function testCreateMediaRepositoryObject() {
        // No need to mock adapters here, until we call `$obj->save()`
        $data = array('id'=> 1, 'name' => 'some_name');
        $obj = $this->_service->create('Media', $data);
        $this->assertEquals($data, $obj->getData());
    }

    public function testGetAllChannelRepositoryObjects_Empty() {
        $this->_mockTheServiceClient('{"status": "ok", "objects": [] }');
        $this->assertCount(0, $this->_service->getAll('Channel'));
    }

    public function testGetAllChannelRepositoryObjects_One() {
        $this->_mockTheServiceClient('{"status": "ok", "object": {"id": 1, "name": "some_name"} }');
        $channel = $this->_service->get('Channel', 1);
        $this->assertInstanceOf('Mr\Api\Model\Channel', $channel);
        $this->assertEquals($channel->getId(), 1);
    }

    public function testGetAllChannelRepositoryObjects_Two() {
        $this->_mockTheServiceClient('{"status": "ok", "objects": [{"id": 1, "name": "some_name"}, {"id": 2, "name": "some_other_name"} ] }');

        $channels = $this->_service->getAll('Channel');
        $this->assertCount(2, $channels);
        $this->assertEquals(1, $channels[0]->getId());
        $this->assertEquals(2, $channels[1]->getId());
    }
}
