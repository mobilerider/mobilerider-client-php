<?php 

namespace MrTest\Api\Http;

use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\AbstractClient;

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

	    // Adds mock basic OK response
	    $this->_client->addResponse();
	    // Send request
	    $this->_client->get($path);
	    // Gets message objects
	    $request = $this->_client->getRequest();
	    $response = $this->_client->getResponse();

	    // Tests request and response are objects
	    \PHPUnit_Framework_Assert::assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $request);
	    \PHPUnit_Framework_Assert::assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_OBJECT, $response);
	}

	public function testGetRequest()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

		// Adds mock basic OK response
	    $this->_client->addResponse();
	    // Send request
	    $this->_client->get($path, $parameters, $headers);
		// Gets response object
	    $response = $this->_client->getResponse();

	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}

	public function testPostRequest()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

		// Adds mock basic OK response
	    $this->_client->addResponse();
	    // Send request
	    $this->_client->post($path, $parameters, $headers);
		// Gets response object
	    $response = $this->_client->getResponse();

	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}

	public function testPutRequest()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

		// Adds mock basic OK response
	    $this->_client->addResponse();
	    // Send request
	    $this->_client->put($path, $parameters, $headers);
		// Gets response object
	    $response = $this->_client->getResponse();

	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}

	public function testDeleteRequest()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

		// Adds mock basic OK response
	    $this->_client->addResponse();
	    // Send request
	    $this->_client->delete($path, $parameters, $headers);
		// Gets response object
	    $response = $this->_client->getResponse();

	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}

	public function testGenericRequest()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');

		// Adds mock basic OK response
	    $this->_client->addResponse();

	    // Send GET request
	    $method = AbstractClient::METHOD_GET;
	    $this->_client->request($method, $path, $parameters, $headers);
	    $request = $this->_client->getRequest();
	    \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
	    $response = $this->_client->getResponse();
	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());

	    // Send POST request
	    $method = AbstractClient::METHOD_POST;
	    $this->_client->request($method, $path, $parameters, $headers);
	    $request = $this->_client->getRequest();
	    \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
	    $response = $this->_client->getResponse();
	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());

	    // Send PUT request
	    $method = AbstractClient::METHOD_PUT;
	    $this->_client->request($method, $path, $parameters, $headers);
	    $request = $this->_client->getRequest();
	    \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
	    $response = $this->_client->getResponse();
	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());

	    // Send DELETE request
	    $method = AbstractClient::METHOD_DELETE;
	    $this->_client->request($method, $path, $parameters, $headers);
	    $request = $this->_client->getRequest();
	    \PHPUnit_Framework_Assert::assertEquals($method, $request->getMethod());
	    $response = $this->_client->getResponse();
	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}

	public function testJsonDataResponse()
	{
		$path = '/test/path';
		$headers = array('h1' => 'a', 'h2' => 'b');
        $parameters = array('p1' => 'c', 'p2' => 'd');
        $method = AbstractClient::METHOD_GET;
        $responseContent = json_encode(array('status' => 'ok', 'data' => 123, 'message' => 'This is a test'));

		// Adds mock response with content
	    $this->_client->addResponse(Response::STATUS_OK, '', $responseContent);
	    // Send request
	    $this->_client->get($path, $parameters, $headers);
		// Gets response object
	    $response = $this->_client->getResponse();

	    \PHPUnit_Framework_Assert::assertEquals(Response::STATUS_OK, $response->getStatus());
	}
}