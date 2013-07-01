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

	protected function getRepository($model)
	{
		$repoName = self::REPO_NAMESPACE . $model . 'Repository';
		return new $repoName($this->_client);
	}

	public function get($model, $id)
	{
		$repo = $this->getRepository($model);

		return $repo->get($id);
	}

	public function getAll($model)
	{
		$repo = $this->getRepository($model);

		return $repo->getAll();
	}
}