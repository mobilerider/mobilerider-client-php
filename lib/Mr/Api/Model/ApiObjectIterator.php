<?php

namespace Mr\Api\Model;

class ApiObjectIterator implements \Iterator
{
    private $_objects = array();
    private $_index = 0;
    private $_collection;

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
            unset($this->_objects);
            unset($this->_collection);
        }

        return $valid;
    }
}