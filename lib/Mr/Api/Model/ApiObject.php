<?php

namespace Mr\Api\Model;

use Mr\Exception\InvalidRepositoryException;
use Mr\Exception\InvalidFormatException;
use Mr\Exception\InvalidFieldException;

use Mr\Api\Util\Validator;

/**
 * ApiObject Class file
 *
 * PHP Version 5.3
 *
 * @category Class
 * @package  Mr\Api\Model
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */

/**
 * ApiObject Class
 *
 * Application class
 *
 * @category Class
 * @package  Mr\Api\Model
 * @author   Michel Perez <michel.perez@mobilerider.com>
 * @license  Copyright (c) 2013 MobileRider Networks LLC
 * @link     https://github.com/mobilerider/mobilerider-php-sdk/
 */
abstract class ApiObject
{
    const STATUS_VALID = 'valid';

    private $_uid;

    /**
    * var array
    */
    protected $_data = array();
    /**
    * var boolean
    */
    protected $_isModified;
    /**
    * var Mr\Api\Repository\ApiRepository
    */
    protected $_repo;

    public function __construct($repository, $data = null)
    {
        $this->_repo = $repository;

        if (!empty($data)) {
            $this->setData($data);
            $this->_isModified = false;
        }
    }

    public function getRepository()
    {
        return $this->_repo;
    }

    /**
    * Returns current model name, eg: Media
    *
    * @return string
    */
    public function getModel()
    {
        preg_match("/([^\\\]+$)/", get_called_class(), $matches);
        return $matches[0];
    }

    /**
    * Returns field name of the string representing this object
    *
    * @return string
    */
    public abstract function getStringField();

    /**
    * Returns field name of the primary key
    *
    * @return string
    */
    public function getKeyField()
    {
        return 'id';
    }

    /**
    * Returns TRUE if the objects has been modified after its creation
    *
    * @return boolean
    */
    public function isModified()
    {
        return $this->_isModified;
    }

    /**
    * Returns TRUE if the object is new
    *
    * @return boolean
    */
    public function isNew()
    {
        return $this->getId() == null;
    }

    /**
    * Returns id value
    *
    * @param $id mixed
    */
    protected function setId($id = null)
    {
        $idField = $this->getKeyField();
        $this->{$idField} = $id;
    }

    /**
    * Returns a unique id if remote id is set, otherwise returns null
    *
    * @return mixed
    */
    public function getId()
    {
        return $this->getRemoteId() ? $this->getClientId() : null;
    }

    /**
     * Returns a unique id set for this object once loaded
     */
    public function getClientId()
    {
        if (!$this->_uid) {
            $this->_uid = uniqid();
        }

        return $this->_uid;
    }

    /**
    * Returns id value
    *
    * @return mixed
    */
    public function getRemoteId()
    {
        $idField = $this->getKeyField();
        return $this->{$idField};
    }

    /**
    * Returns raw field values
    *
    * @return array
    */
    public function getData()
    {
        return $this->_data;
    }

    /**
    * Returns validators to apply to this object
    *
    * @return array
    */
    public function getValidators()
    {
        array();
    }

    /**
    * Sets given data as part of this object field values.
    * Data parameter needs to be an object or an array, otherwise
    * an exception is thrown.
    *
    * @throws InvalidFormatException
    * @param ApiObject | object | array $data
    */
    public function setData($data)
    {
        if ($data instanceof ApiObject) {
            $data = $data->getData();
        }

        if (is_object($data)) {
            foreach (get_object_vars($data) as $name => $value) {
                $this->{$name} = $value;
            }
        } else if (is_array($data)) {
            $this->_data = array_merge($this->_data, $data);
        } else {
            throw new InvalidFormatException();
        }

        $this->_isModified = true;
    }

    /**
    * Sets given data as part of this object field values.
    * Except primary key field.
    * Data parameter needs to be an object or an array, otherwise
    * an exception is thrown.
    *
    * @throws InvalidFormatException
    * @param ApiObject | object | array $data
    */
    public function updateData($data)
    {
        if ($data instanceof ApiObject) {
            $data = $data->getData();
        } else if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            unset($data[$this->getKeyField()]);
        } else {
            throw new InvalidFormatException();
        }

        $this->setData($data);
    }

    /**
     * Validates object data
     *
     * @return boolean
     */
    public function validate()
    {
        $validators = $this->getValidators();

        foreach ($validators as $field => $validator) {
            $value = isset($this->_data[$field]) ? $this->_data[$field] : null;
            list($valid, $message) = Validator::validate($value, $validator);

            if (!$valid) {
                throw new InvalidFieldException($field, $message);
            }
        }

        return self::STATUS_VALID;
    }

    public function beforeSave()
    {
        // To implement in child classes
    }

    public function afterSave()
    {
        // To implement in child classes
    }

    public function beforeDelete()
    {
        // To implement in child classes
    }

    public function afterDelete()
    {
        // To implement in child classes
    }

    /**
    * Saves current object using owner repository
    */
    public final function save()
    {
        if (!$this->_repo) {
            throw new InvalidRepositoryException();
        }

        $this->_repo->save($this);
    }

    /**
    * Delete current object using owner repository
    */
    public final function delete()
    {
        if (!$this->_repo) {
            throw new InvalidRepositoryException();
        }

        $this->_repo->delete($this);
    }

    /**
     * <b>Magic method</b>. Returns value of specified property
     *
     * @param string $name property name
     *
     * @return mixed
     */

    public function __get($name)
    {
        return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
    }

    /**
     * <b>Magic method</b>. Sets value of a dynamic property
     *
     * @param string $name property name
     * @param mixed  $value new value
     *
     * @return mixed param value
     */
    public function __set($name, $value)
    {
        $oldValue = $this->{$name};

        if ($modified = $oldValue !== $value) {
            $this->_data[$name] = $value;
        }

        $this->_isModified = $this->_isModified || $modified;
    }

    /**
     * <b>Magic method</b>. Unsets a property
     *
     * @param string $name property name
     *
     * @return void
     */
    public function __unset($name) {
        unset($this->_data[$name]);
    }

    /**
     * <b>Magic method</b>. Checks if property exists
     *
     * @param string $name property name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_data);
    }

    public function __toString()
    {
        $strField = $this->getStringField();
        return $this->{$strField} ? $this->{$strField} : var_export($this->_data, true);
    }

    /**
    * For internal use ONLY
    *
    */
    public final function saved($data = null)
    {
        if ($data) {
            $this->setData($data);
            $this->validate();
        }
        $this->_isModified = false;
    }

    /**
    * For internal use ONLY
    *
    */
    public final function deleted()
    {
        $this->setId();
        $this->_isModified = false;
        $this->_repo = null;
    }
}
