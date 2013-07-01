<?php

namespace Mr\Api\Repository;

use Mr\Api\ClientInterface;

abstract class ApiRepository
{
	const API_URL_PREFIX = 'api';
	const STATUS_OK = 'ok';
	const MODEL_NAMESPACE = 'Mr\\Api\\Model\\';

	/**
	* var ClientInterface
	*/
	protected $_client;

	public function __construct(ClientInterface $client)
	{
		$this->_client = $client;
	}

	/**
	* Returns current model name, eg: Media
	*/
	public function getModel()
	{
		preg_match("/([^\\\]+$)/", get_called_class(), $matches);
		return str_replace('Repository', '', $matches[0]);
	}

	protected function validateResponse($response)
	{
		return is_object($response) && $response->status == self::STATUS_OK;
	}

	public function create($data = null)
	{
		$modelClass = self::MODEL_NAMESPACE . $this->getModel();
		return new $modelClass($this, $data);
	}

	public function get($id)
	{
		if (!$id || !is_numeric($id))
			throw new Exception("Invalid Id");

		$path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $id);
		
		$response = $this->_client->get($path);

		if ($this->validateResponse($response)) {
			return $this->create($response->object);
		}

		return null;
	}

	public function getAll()
	{
		$path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
		
		$response = $this->_client->get($path);
		$results = array();

		if ($this->validateResponse($response)) {
			foreach ($response->objects as $object) {
				$results[] = $this->create($object);
			}
		}

		return $results;
	}

	public function save(ApiObject $object)
	{
		if ($object->isNew()) {
			$method = AbstractClient::METHOD_POST;
			$path = sprintf("%s/%s", self::API_URL_PREFIX, $this->getModel());
		} else {
			$method = AbstractClient::METHOD_PUT;
			$path = sprintf("%s/%s/%d", self::API_URL_PREFIX, $this->getModel(), $object->getId());
		}

		$params = array('JSON' => $object->getData());

		$this->_client->request($method, $path, $params);
	}
}