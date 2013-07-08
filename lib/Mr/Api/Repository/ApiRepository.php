<?php

namespace Mr\Api\Repository;

// Model
use Mr\Api\Model\ApiObject;

// Client
use Mr\Api\ClientInterface;
use Mr\Api\AbstractClient;

// Exceptions
use Mr\Exception\ServerErrorException;
use Mr\Exception\InvalidResponseException;
use Mr\Exception\DeniedEntityAccessException;

/** 
 * ApiRepository Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Repository
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * ApiRepository Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Repository
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
abstract class ApiRepository
{
    const API_URL_PREFIX = 'api';
    const MODEL_NAMESPACE = 'Mr\\Api\\Model\\';

    const STATUS_OK = 'ok';
    const STATUS_NOT_FOUND_PATTERN = 'Unknown %s';
    const STATUS_DENIED_ACCESS = 'Access denied';

    /**
    * var ClientInterface
    */
    protected $_client;

    /**
    * Constructor
    *
    * @param ClientInterface $client Client used to retrieve data
    * @return void
    */
    public function __construct(ClientInterface $client)
    {
        $this->_client = $client;
    }

    /**
    * Returns current model name, eg: Media
    *
    * @return string
    */
    public function getModel()
    {
        preg_match("/([^\\\]+$)/", get_called_class(), $matches);
        return str_replace('Repository', '', $matches[0]);
    }

    /**
    * Returns TRUE if given response is valid or throw an exception otherwise
    *
    * @throws DeniedEntityAccessException
    * @throws ServerErrorException
    * @throws InvalidResponseException
    *
    * @param $response mixed
    * @return boolean
    */
    protected function validateResponse($response)
    {
        $success = is_object($response) && (
            self::STATUS_OK == $response->status ||
            // Avoid throwing exception if the entity was not found, instead return null
            sprintf(self::STATUS_NOT_FOUND_PATTERN, strtolower($this->getModel())) == $response->status
        );

        if (!$success && self::STATUS_DENIED_ACCESS == $response->status) {
            throw new DeniedEntityAccessException();
        } 

        if (!$success) {
            if (is_object($response) && $response->status) {
                throw new ServerErrorException($response->status);
            } else {
                throw new InvalidResponseException();
            }
        }

        return $success;
    }

    /**
    * Returns a new model object. 
    * It does not execute any persistent action
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

        if ($this->validateResponse($response) && !empty($response->object)) {
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

        if ($this->validateResponse($response) && !empty($response->objects)) {
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
    * @return void
    */
    public function delete(ApiObject $object)
    {
        $object->beforeDelete();

        $path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $object->getId());

        $this->_client->delete($path);

        $object->deleted();

        $object->afterDelete();
    }

    /**
    * Saves given model object.
    *
    * @throws InvalidDataOperationException
    *
    * @param $object Mr\Api\Model\ApiObject
    * @return void
    */
    public function save(ApiObject $object)
    {
        if (ApiObject::STATUS_VALID != ($msg = $object->validate())) {
            throw new InvalidDataOperationException($msg, 'Save object');
        }

        $object->beforeSave();

        if ($object->isNew()) {
            $method = AbstractClient::METHOD_POST;
            $path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
        } else {
            $method = AbstractClient::METHOD_PUT;
            $path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $object->getId());
        }

        $params = array('JSON' => $object->getData());
        $response = $this->_client->request($method, $path, $params);

        if ($object->isNew() && $this->validateResponse($response)) {
            $object->saved($response->object);
        } else {
            $object->saved();
        }

        $object->afterSave();
    }
}