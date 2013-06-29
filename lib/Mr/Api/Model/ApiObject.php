<?php 

namespace Mr\Api\Model;

abstract class ApiObject
{
	protected $_data;
	protected $_isModified;
	protected $_repo;

	public function __construct($repository, $data = array())
	{
		$this->_repo = $repository;
		$this->setData($data);
	}

	/**
	* Returns current model name, eg: Media
	*/
	public function getModel()
	{
		preg_match("/([^\\\]+$)/", get_called_class(), $matches);
		return $matches[0];
	}

	public function isModified()
	{
		return $this->_isModified;
	}

	public function getData()
	{
		return $this->_data;
	}

	public function setData($data)
	{
		if (is_object($data)) {
			foreach (get_object_vars($data) as $name => $value) {
				$this->{$name} = $value;
			}
		} else if (is_array($data)) {
			$this->_data = array_merge($this->_data, $data);
		} else {
			throw new Exception("Invalid data format");
		}
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
}