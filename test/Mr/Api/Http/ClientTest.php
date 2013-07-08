<?php 

namespace MrTest\Api\Http;

use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\AbstractClient;
use Mr\Api\Http\Adapter\MockAdapter;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    const HOST = 'http://test.host.com';
    const USERNAME = 'user';
    const PASSWORD = 'pass';
    const CLIENT_NAMESPACE = 'Mr\\Api\\Http\\';

    protected $_client;
    protected $_mockClient;

    public function setUp()
    {
        $this->_client = new Client(self::HOST, self::USERNAME, self::PASSWORD);
    }

    public function testMessagesTypes()
    {
        $path = '/test/path';

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);

        // Send request
        $this->_client->get($path);

        // Gets message objects
        $request = $this->_client->getRequest();
        $response = $this->_client->getResponse();

        // Tests request and response are objects
        \PHPUnit_Framework_Assert::assertInstanceOf(self::CLIENT_NAMESPACE . 'Request', $request);
        \PHPUnit_Framework_Assert::assertInstanceOf(self::CLIENT_NAMESPACE . 'Response', $response);
    }

    public function testGetRequest()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);
        // Send request
        $this->_client->get($path, $parameters, $headers);
        // Gets request object
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals(AbstractClient::METHOD_GET, $request->getMethod());
        // Gets response object
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }

    public function testPostRequest()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);
        // Send request
        $this->_client->post($path, $parameters, $headers);
        // Gets request object
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals(AbstractClient::METHOD_POST, $request->getMethod());
        // Gets response object
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }

    public function testPutRequest()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);
        // Send request
        $this->_client->put($path, $parameters, $headers);
        // Gets request object
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals(AbstractClient::METHOD_PUT, $request->getMethod());
        // Gets response object
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }

    public function testDeleteRequest()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);
        // Send request
        $this->_client->delete($path, $parameters, $headers);
        // Gets request object
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals(AbstractClient::METHOD_DELETE, $request->getMethod());
        // Gets response object
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }

    public function testGenericRequest()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy();
        $mockAdapter->addResponseBy();
        $mockAdapter->addResponseBy();
        $mockAdapter->addResponseBy();
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);

        // Send GET request
        $method = AbstractClient::METHOD_GET;
        $this->_client->request($method, $path, $parameters, $headers);
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());

        // Send POST request
        $method = AbstractClient::METHOD_POST;
        $this->_client->request($method, $path, $parameters, $headers);
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());

        // Send PUT request
        $method = AbstractClient::METHOD_PUT;
        $this->_client->request($method, $path, $parameters, $headers);
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());

        // Send DELETE request
        $method = AbstractClient::METHOD_DELETE;
        $this->_client->request($method, $path, $parameters, $headers);
        $request = $this->_client->getRequest();
        \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
        $response = $this->_client->getResponse();
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }

    public function testJsonDataResponse()
    {
        $path = '/test/path';
        $headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');
        $method = AbstractClient::METHOD_GET;
        $responseData = array('status' => 'ok', 'data' => 123, 'message' => 'This is a test');
        $responseContent = json_encode($responseData);

        $mockAdapter = new MockAdapter();
        // Adds mock basic OK response
        $mockAdapter->addResponseBy(Response::STATUS_OK, '', $responseContent);
        // Add mock adapter
        $this->_client->setAdapter($mockAdapter);
        // Send request
        $data = $this->_client->get($path, $parameters, $headers);
        // Gets response object
        $response = $this->_client->getResponse();

        // Content is being returned correctly
        \PHPUnit_Framework_Assert::assertJson($response->getRawContent());
        \PHPUnit_Framework_Assert::assertJsonStringEqualsJsonString($responseContent, $response->getRawContent());
        // Content is being parsed correctly (json)
        \PHPUnit_Framework_Assert::assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $data);
        // Check data parsed
        foreach ($responseData as $key => $value) {
            \PHPUnit_Framework_Assert::assertObjectHasAttribute($key, $data);      
            \PHPUnit_Framework_Assert::assertEquals($value, $data->{$key}); 
        }
        
        \PHPUnit_Framework_Assert::assertTrue($response->isOK());
    }
}