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
use Mr\Exception\MrException;
use Mr\Exception\ServerErrorException;
use Mr\Exception\InvalidResponseException;
use Mr\Exception\DeniedEntityAccessException;
use Mr\Exception\MissingResponseAttributesException;
use Mr\Exception\MultipleServerErrorsException;

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

    const MSG_WITH_ERRORS = "Some errors ocurred during this action.";

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
    * Returns current client object
    *
    * @return Mr\Api\AbstractClient
    */
    public function getClient()
    {
        return $this->_client;
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
    protected function validateResponse($response, $method)
    {
        if (empty($response)) {
            throw new InvalidResponseException('Empty response.');
        }

        $responseAttrs = get_object_vars($response);

        if (!array_key_exists('status', $responseAttrs)) {
            throw new MissingResponseAttributesException(array('status'));
        }

        $success = is_object($response) && self::STATUS_OK == $response->status;

        if (!$success && self::MSG_WITH_ERRORS == $response->status) {
            throw new MultipleServerErrorsException($response, $method);
        }

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

        if (in_array($method, array(AbstractClient::METHOD_POST, AbstractClient::METHOD_GET))) {
            if (!array_key_exists('objects', $responseAttrs) && !array_key_exists('object', $responseAttrs)) {
                throw new MissingResponseAttributesException(array('object(s)'));
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
        if (!$id || !is_numeric($id)) {
            throw new MrException("Invalid Id");
        }

        $path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $id);
        
        $response = $this->_client->get($path);

        if ($this->validateResponse($response, AbstractClient::METHOD_GET) && !empty($response->object)) {
            return $this->create($response->object);
        }

        return null;
    }

    /**
    * Returns a objects from this model according to given filters with lazy load.
    *
    * @param array | null $filters Filters for the server request, only page supported for now
    * @param &array | null $metadata Variable to store objects metadata in
    * @return ApiObjectCollection
    */
    public function getAll($filters = array(), &$metadata = null)
    {
        $page = isset($filters['page']) ? $filters['page'] : $this->_metadataDefaults['page'];

        return new ApiObjectCollection($this, $page);
    }

    /**
    * Returns a objects from this model according with given filters (no lazy load).
    * Objects are retrieved and returned at once.
    *
    * @param array | null $filters Filters for the server request, only page supported for now
    * @param &array | null $metadata Variable to store objects metadata in
    * @return array
    */
    public function getAllRecords($filters = array(), &$metadata = null)
    {
        $path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
            
        $response = $this->_client->get($path, $this->validateFilters($filters));
        $results = array();

        if ($metadata !== null && is_array($metadata)) {
            $metadata = array_merge($metadata, $this->validateMetadata($response));
        }

        if ($this->validateResponse($response, AbstractClient::METHOD_GET) && !empty($response->objects)) {
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

    protected function preSave($object)
    {
        $new = array();
        $modified = array();

        if (empty($object)) {
            throw new InvalidDataOperationException('Object is empty', 'Save object');
        }

        $objects = !is_array($object) && !($object instanceof ApiObjectCollection) ? array($object) : $object;

        foreach ($objects as $object) {
            if (ApiObject::STATUS_VALID != ($msg = $object->validate())) {
                throw new InvalidDataOperationException($msg, 'Save object');
            }

            $object->beforeSave();

            if ($object->isNew()) {
                $new[] = $object;
            } else {
                $modified[] = $object;
            }
        }    

        return array($new, $modified);
    }

    protected function getRequestParams(array $objects)
    {
        if (count($objects) == 1) {
            $object = $objects[0];
            $data = $object->getData();
        } else {
            $data = array();

            foreach ($objects as $object) {
                $objectData = $object->getData();
                $data[] = $objectData;
            }
        }

        return array('JSON' => $data);
    }

    protected function postSave($response, $object, $method)
    {
        if ($method == AbstractClient::METHOD_POST || $method == AbstractClient::METHOD_PUT) {
            $this->validateResponse($response, $method);
        }

        if ($method == AbstractClient::METHOD_POST) {
            $data = isset($response->objects) ? $response->objects : $response->object;
            $data = !is_array($data) ? array($data) : $data;
        }

        $objects = !is_array($object) ? array($object) : $object;

        foreach ($objects as $key => $object) {

            if ($object->isNew()) {
                // New object waiting for returned data
                // Data list is assumed to have same order than local objects
                $object->saved($data[$key]);
            } else {
                $object->saved();
            }

            $object->afterSave();
        }
    }

    /**
    * Saves given model object or list of objects.
    *
    * @throws InvalidDataOperationException
    *
    * @param ApiObject | ApiObjectCollection | array $object
    * @return void
    */
    public function save($object)
    {
        list($new, $modified) = $this->preSave($object);

        if (!empty($new)) {
            $path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
            $params = $this->getRequestParams($new);
            $response = $this->_client->post($path, $params);

            $this->postSave($response, $new, AbstractClient::METHOD_POST);
        }

        if (!empty($modified)) {
            if (count($modified) == 1) {
                $path = sprintf("%s/%s/%d", self::API_URL_PREFIX, strtolower($this->getModel()), $object->getId());
            } else {
                $path = sprintf("%s/%s", self::API_URL_PREFIX, strtolower($this->getModel()));
            }

            $params = $this->getRequestParams($modified);
            $response = $this->_client->put($path, $params);

            $this->postSave($response, $modified, AbstractClient::METHOD_PUT);
        }
        
    }
}