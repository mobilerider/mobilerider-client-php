<?php

namespace Mr\Api\Repository;

// Model
use Mr\Api\Model\ApiObject;

// Collection
use Mr\Api\Collection\ApiObjectCollection;

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
    protected $_metadataDefaults = array(
        'total' => 0,
        'pages' => 0,
        'page' => 1,
        'limit' => 20
    );
    protected $_filterDefaults = array(
        'page' => 1
    );

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

    protected function validateMetadata($response)
    {
        // Check for metadata to exists
        $metadata = isset($response->meta) ? get_object_vars($response->meta) : array();
        // Use metadata defaults
        return array_merge($this->_metadataDefaults, $metadata);
    }

    /**
    * Checks if the set of filters given are allowed by the server methods
    *
    * @throws InvalidFiltersException
    * @param array $filters
    * @return array $filters
    */
    protected function validateFilters($filters)
    {
        $filters = !empty($filters) ? $filters : array();
        $filters = is_array($filters) ? $filters : array($filters);
        $filters = array_merge($this->_filterDefaults, $filters);
        
        $diff = array_diff_key($this->_filterDefaults, $filters);

        if (!empty($diff)) {
            throw new InvalidFiltersException($diff, $array_keys($this->_filterDefaults));
        }

        //@TODO: check the type filters

        return $filters;
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
    * @param array | null $filters Filters for the server request, only page supported for now
    * @param &array | null $metadata Variable to store objects metadata in
    * @param boolean $lazy If TRUE this methods sends an inmediate request and returns the results 
    *        from server otherwise none request is done yet and an ApiObjectCollection is returned
    * @return ApiObjectCollection | array
    */
    public function getAll($filters = array(), &$metadata = null, $lazy = true)
    {
        //@TODO: Check metadata provided and lazy flag to avoid any chance of infinite loop
        if (!$lazy) {
            $path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
            
            $response = $this->_client->get($path, $this->validateFilters($filters));
            $results = array();

            if (!empty($metadata)) {
                $metadata = array_merge($metadata, $this->validateMetadata($response));
            }

            if ($this->validateResponse($response) && !empty($response->objects)) {
                foreach ($response->objects as $object) {
                    $results[] = $this->create($object);
                }
            }

            return $results;
        }

        $page = isset($filters['page']) ? $filters['page'] : $this->_metadataDefaults['page'];

        return new ApiObjectCollection($this, $page);
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