<?php 

namespace Mr\Api\Model;

use Mr\Exception\InvalidRepositoryException;

abstract class ApiObject
{
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
		if ($data) {
			$this->setData($data);
		}
		$this->_isModified = false;
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
		return 'ID';
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
	* Returns id value
	*
	* @return mixed
	*/
	public function getId()
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
	* Sets given data as part of this object field values.
	* Data parameter needs to be an object or an array, otherwise 
	* an exception is thrown.
	*
	* @param $data mixed
	*/
	public function setData($data)
	{
		if (is_object($data)) {
			foreach (get_object_vars($data) as $name => $value) {
				$this->{$name} = $value;
			}
		} else if (is_array($data)) {
			$this->_data = array_merge($this->_data, $data);
			$this->_isModified = true;
		} else {
			throw new \Exception("Invalid data format");
		}
	}

	/**
	* Saves current object using owner repository
	*/
	public function save()
	{
		if (!$this->_repo) {
			throw new InvalidRepositoryException();
		}

		$this->_repo->save($this);
	}

	/**
	* Delete current object using owner repository
	*/
	public function delete()
	{
		if (!$this->_repo) {
			throw new InvalidRepositoryException();
		}

		$this->_repo->delete($this);
	}

	/**
     * <b>Magic method</b>. Returns value of specified field
     *
     * @param string $name Field name
     *
     * @return mixed
     */

    public function __get($name)
    {
        return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
    }

    /**
     * <b>Magic method</b>. Sets value of field in row
     *
     * @param string $name  Field name
     * @param mixed  $value New value
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

    public function __toString()
    {
    	$strField = $this->getStringField();
    	return $this->{$strField} ? $this->{$strField} : var_export($this->_data);
    }

    /**
    * For internal use ONLY
    *
    */
    public function saved($data = null)
    {
    	if ($data) {
    		$this->setData($data);
    	}
    	$this->_isModified = false;
    }

    /**
    * For internal use ONLY
    *
    */
    public function deleted()
    {
    	$this->setId();
    	$this->_isModified = false;
    	$this->_repo = null;
    }
}