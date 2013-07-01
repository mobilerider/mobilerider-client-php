<?php

namespace Mr\Api\Repository;

use Mr\Api\ClientInterface;
use Mr\Api\AbstractClient;
use Mr\Exception;
use Mr\Api\Model;
use Mr\Api\Model\ApiObject;

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

	/**
	* Returns TRUE if given response is valid or throw an exception otherwise
	*
	* @param $response mixed
	* @return boolean
	*/
	protected function validateResponse($response)
	{
		$success = is_object($response) && $response->status == self::STATUS_OK;

		if (!$success) {
			throw new InvalidResponseException();
		}

		return $success;
	}

	/**
	* Returns a new model object. 
	* It does not execute any persisten action
	*
	* @param $data object | array
	* @return Mr\Api\Model\ApiObject
	*/
	public function create($data = null)
	{
		$modelClass = self::MODEL_NAMESPACE . $this->getModel();
		return new $modelClass($this, $data);
	}

	/**
	* Returns an object by its given id. 
	*
	* @param $id mixed 
	* @return Mr\Api\Model\ApiObject
	*/
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

	/**
	* Returns a all objects from this model. 
	*
	* @return array
	*/
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

	/**
	* Deletes given model object.
	*
	* @param $object Mr\Api\Model\ApiObject
	*/
	public function delete(ApiObject $object)
	{
		$path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $object->getId());

		$this->_client->delete($path);

		$object->deleted();
	}

	/**
	* Saves given model object.
	*
	* @param $object Mr\Api\Model\ApiObject
	*/
	public function save(ApiObject $object)
	{
		if ($object->isNew()) {
			$method = AbstractClient::METHOD_POST;
			$path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
		} else {
			$method = AbstractClient::METHOD_PUT;
			$path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $object->getId());
		}

		$params = array('JSON' => $object->getData());
		$data = $this->_client->request($method, $path, $params);
		$object->saved($object->isNew() ? $data : null);
	}
}