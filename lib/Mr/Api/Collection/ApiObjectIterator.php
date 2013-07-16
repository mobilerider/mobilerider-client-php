<?php

namespace Mr\Api\Collection;

class ApiObjectIterator implements \Iterator
{
    protected $_objects = array();
    protected $_index = 0;
    protected $_collection;

    public function __construct(ApiObjectCollection $collection) 
    {
        $this->_collection = $collection;    
    }

    function rewind()
    {
        $this->_collection->setCurrentPage(1);
        $this->_objects = $this->_collection->getObjects();
        $this->_index = 0;
    }

    function current() 
    {
        return $this->_objects[$this->_index];
    }

    function key() 
    {
        return $this->_index;
    }

    function next() 
    {
        // Check if the new items is still available in current set of objects
        // If NOT check if the collection has a next page of objects
        if (++$this->_index >= count($this->_objects) && $this->_collection->hasNextPage()) {
            // Increase page
            $this->_collection->increasePage();
            // Get the new page containing a new set of objects
            $this->_objects = array_merge($this->_objects, $this->_collection->getObjects());
        }
    }

    function valid()
    {
        $valid = isset($this->_objects[$this->_index]);

        if (!$valid) {
            $this->_objects = null;
            $this->_collection = null;
        }

        return $valid;
    }
}