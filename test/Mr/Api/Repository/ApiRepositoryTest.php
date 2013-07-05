<?php 

namespace MrTest\Api\Repository;

use Mr\Api\Repository\ApiRepository;
use Mr\Api\AbstractClient;

class ApiRepositoryTest extends \PHPUnit_Framework_TestCase
{
	protected $_client;

	/*public function setUp()
	{
		$channel1 = array(
			'id' => 1,
			'url' => 'http://site.channel.com',
			'name' => 'Channel 1'
		);

		$channels = array(
			'status' => ApiRepository::STATUS_OK,
			'objects' => array(
				$channel1,
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

		$this->_client = new Client(self::HOST, self::USERNAME, self::PASSWORD);

		//$this->_client->$this->_client->addResponse(Response::STATUS_OK, '', $responseContent);
	}*/
}
