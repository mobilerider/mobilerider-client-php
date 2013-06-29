<?php 

namespace Mr\Api;

use Mr\Api\Http\Client;

class Service
{
	const API_HOST = 'api.devmobilerider.com';
	const REPO_NAMESPACE = 'Mr\\Api\\Repository\\';

	/**
	* var ClientInterface
	*/
	protected $_client;

	public function __construct($username, $password)
	{
		$this->_client = new Client(self::API_HOST, $username, $password);
	}

	public function get($model, $id)
	{
		$repoName = self::REPO_NAMESPACE . $model . 'Repository';
		$repo = new $repoName($this->_client);

		return $repo->get($id);
	}

	public function getAll($model)
	{
		$repoName = $model . 'Repository';
		$repo = $repoName($this->_client);

		return $repo->get($id);
	}
}