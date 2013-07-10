<?php

namespace MrTest\Api;

use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\AbstractClient;
use Mr\Api\Service;
use Mr\Api\Http\Adapter\MockAdapter;

class ServiceTest extends \PHPUnit_Framework_TestCase {

    const APP_ID = 'some_app';
    const APP_SECRET = 'some_app_secret';

    protected $_service;
    protected $_mockService;

    public function setUp() {
        $this->_service = new Service(self::APP_ID, self::APP_SECRET);
    }

    public function testCreateChannelRepositoryObject() {
        $data = array('some' => 'data');
        $obj = $this->_service->create('Channel', $data);
        $this->assertEquals($data, $obj->getData());
    }

    public function testCreateMediaRepositoryObject() {
        $data = array('some' => 'data');
        $obj = $this->_service->create('Media', $data);
        $this->assertEquals($data, $obj->getData());
    }

    public function testGetAllChannelRepositoryObjects() {

        $path = '/test/path';
        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy(Response::STATUS_OK, $path, '[]');
        // Add mock adapter
        $service_client = $this->_service->getClient();
        $service_client->setAdapter($mockAdapter);

        $this->assertCount(0, $this->_service->getAll('Channel'));
    }
}
