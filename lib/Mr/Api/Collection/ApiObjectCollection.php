<?php 

namespace Mr\Api\Collection;

use Mr\Api\Repository\ApiRepository;
use Mr\Api\Model\ApiObject;

// Exceptions
use Mr\Exception\InvalidTypeException;

class ApiObjectCollection extends AbstractPaginator implements ApiObjectCollectionInterface
{
    protected $_isInitialized = false;

    // Object storage
    protected $_objects = array();
    protected $_dirtyObjects = array();
    protected $_pages = array();

    // Metadata storage
    protected $_total;
    protected $_limit;

    protected $_repository;

    public function __construct(ApiRepository $repository, $page = 1)
    {
        $this->_repository = $repository;

        if ($page > 1) {
            $this->setCurrentPage($page);
        } else {
            $this->_page = 1;
        }
    }

    /**
    * If the collection is not initialized already, this method
    * loads current page of elements in order to obtain metadata information
    * for first time.
    *
    * @return void
    */
    protected function initialize()
    {
        if (!$this->_isInitialized) {
            $this->load();
            $this->_isInitialized = true;
        }
    }

    /**
    * Check if the given object is a valid ApiObject for this collection, 
    * taking into account the model of the repository attached.
    * Returns the actual given object
    *
    * @throws InvalidRepositoryException
    * @param ApiObject $object
    * @return ApiObject $object
    */
    protected function validateObject(ApiObject $object) 
    {
        if ($object->getModel() != $this->_repository->getModel()) {
            throw new InvalidRepositoryException();
        }

        return $object;
    }

    protected function validateIndex($index)
    {
        return 0 <= $index && $this->count() > $index;
    }

    /**
    * Returns current metadata stored
    *
    * @return array
    */
    protected function getMetadata()
    {
        return array(
            'total' => $this->_total,
            'page' => $this->_page,
            'pages' => $this->_pageTotal,
            'limit' => $this->_limit
        );
    }

    /**
    * Checks if an object is already loaded by its given primary key.
    * Returns TRUE if object is loaded.
    *
    * @param mixed $id
    * @return boolean
    */
    protected function isObjectLoaded($id)
    {
        return $this->_isInitialized && array_key_exists($id, $this->_objects);
    }

    /**
    * Checks if a page is already loaded by its given index.
    * Returns TRUE if page is loaded.
    *
    * @param integer $page
    * @return boolean
    */
    protected function isPageLoaded($page)
    {
        return $this->_isInitialized && array_key_exists(((int)$page - 1), $this->_pages);
    }

    /**
    * Checks if all objects have been loaded already.
    * Returns TRUE all objects are loaded.
    *
    * @return boolean
    */
    protected function isFullyLoaded()
    {
        return $this->_isInitialized && count($this->_objects) >= $this->_total;
    }

    /**
    * Checks if an object index has been loaded already.
    * Returns TRUE if the index is loaded.
    *
    * @return boolean
    */
    protected function isIndexLoaded($index)
    {
        return $this->_isInitialized && $this->isPageLoaded($this->getPageByIndex($index));
    }

    /**
    * Checks if received metadata is equal to the one stored.
    * If metadata data are different the collection gets cleared unless
    * it has not been initialized yet.
    *
    * @param array $metadata Metadata returned by the server to compare to
    * @return boolean
    */
    protected function isMetadataUpToDate($metadata) 
    {
        $currentMetadata = $this->getMetadata();

        if ($currentMetadata['total'] != $metadata['total'] ||
            $currentMetadata['pages'] != $metadata['pages'] ||
            $currentMetadata['limit'] != $metadata['limit']) {

            $this->_total = isset($metadata['total']) ? (int)$metadata['total'] : $this->_total;
            $this->_limit = isset($metadata['limit']) ? (int)$metadata['limit'] : $this->_limit;
            $this->_pageTotal = isset($metadata['pages']) ? (int)$metadata['pages'] : $this->_pageTotal;

            return !$this->_isInitialized;
        }

        return true;
    }

    /**
    * Loads from repository the set of objects that belongs to the given page.
    * If none page is given the it uses the current page.
    * If the page is already loaded this method does nothing.
    *
    * @param integer page
    * @return void
    */
    protected function load($page = 0)
    {
        $page = $page ? $this->validatePage($page) : $this->_page;
        $pageIndex = (int)$page - 1;

        if (!$this->isPageLoaded($page)) {
            $metadata = $this->getMetadata();

            $objects = $this->_repository->getAll(
                array('page' => $page),
                $metadata,
                false // IMPORTANT, to avoid an infinite loop
            );

            if (!$this->isMetadataUpToDate($metadata)) {
                $this->clear();
            }

            $this->_pages[$pageIndex] = array();

            foreach ($objects as $object) {
                if ($this->validateObject($object)) {
                    // Add object object by primery key
                    $this->_objects[$object->getId()] = $object;
                    // Add object object to its page
                    $this->_pages[$pageIndex][] = $object;
                }
            }
        }

        return $this->_pages[$pageIndex];
    }

    /**
    * Loads all objects from all pages making iterative single page loads.
    *
    * @return void
    */
    protected function loadAll()
    {
        $this->setCurrentPage(1);
        $count = 0;

        while (!$this->isFullyLoaded() && $count < $this->_pages) {
            $this->load();
            $this->increasePage();
            $count++;
        }
    }

    protected function obtainIdFrom($object) 
    {
        return $id = is_object($object) && $this->validateObject($object) ? $object->getId() : $object;
    }

    /**
    * Returns the index of a given object or primary key.
    * This method foces a massive load of all objects.
    *
    * @param mixed $object
    * @return integer
    */
    public function getIndexOf($object)
    {
        $id = $this->obtainIdFrom($object);

        //@TODO: Avoid full load here by using getIds. Use smarter method.
        return array_search($this->getIds(), $id);
    }

    /**
    * Returns page number of the given object
    * This method foces a massive load of all objects.
    *
    * @param mixed $object
    * @return integer
    */
    public function getPageOf($object)
    {
        return $this->getPageByIndex($this->getIndexOf($object));
    }

    /**
    * Returns the index of an object based on the given page number
    *
    * @param integer $index
    * @return integer
    */
    public function getPageByIndex($index)
    {
        return $index ? ceil($index / $this->_limit) : 1;
    }

    /**
    * Methods to satisfy the ApiObjectCollectionInterface Interface implementation
    */

    /**
    * Returns the name of the contained objects model.
    *
    * @return string
    */
    public function getModel()
    {
        return $this->_repository->getModel();
    }

    /**
    * Returns a list of ids from all objects
    * This method forces a massive load of all objects
    *
    * @param boolean $onlyLoaded If TRUE returns only ids from those already loaded objects
    * @return array
    */
    public function getIds($onlyLoaded = false)
    {
        $this->initialize();

        if (!$onlyLoaded) {
            $this->loadAll();
        }

        return array_keys($this->_objects);
    }

    /**
    * Returns an object by its given numeric index.
    * If the object is not found returns NULL
    *
    * @param integer $index
    * @return ApiObject | null
    */
    public function getByIndex($index)
    {
        if ($this->validateIndex($index)) {
            $page = $this->getPageByIndex($index);

            if (!$this->isIndexLoaded($index)) {
                $this->load($page);
            }
            // Gets objects from requested page
            $objects = $this->_pages[$page - 1];
            // return object from computed offset
            return $objects[$index - (($page - 1) * $this->_limit)];
        } 

        return null;
    }

    /**
    * Returns an object by its primary key
    * If the object is not found returns NULL
    *
    * @param mixed $id
    * @return ApiObject | null
    */
    public function get($id)
    {
        $this->initialize();

        if ($this->exists($id)) {
            return $this->_objects[$id];
        }   

        return null;
    }

    /**
    * Returns a list containing all objects.
    * This method forces a massive load of all objects.
    *
    * @return array
    */
    public function toArray()
    {
        $this->initialize();

        $this->loadAll();

        return array_values($this->_objects);
    }

    /**
    * Cleans internal storage including all objects and pages.
    *
    * @return void
    */
    public function clear()
    {
        $this->_objects = array();
        $this->_pages = array();
    }

    /**
    * Adds a new object to this collection to be saved.
    * The object attached will NOT be accessible until the collection be saved
    *
    * @param ApiObject
    * @return void
    */
    public function add(ApiObject $object)
    {
        $this->_dirtyObjects[] = $this->validateObject($object);
    }

    /**
    * Updates objects data with the data provided from an associative array or another object.
    * If an object is given, only the similar stored object is updated.
    * Returns TRUE if the object(s) was updated
    *
    * @param ApiObject | array $data
    * @param mixed $object
    * @return boolean
    */
    public function update($data, $object = null)
    {
        $this->initialize();

        if (!empty($object)) {
            $id = $this->obtainIdFrom($object);

            if ($internalObj = $this->get($id)) {
                $internalObj->updateData($data);

                return true;
            }

        } else {
            $this->loadAll();

            foreach ($this->_objects as $internalObj) {
                $internalObj->updateData($data);
            }

            return true;
        }

        return false;
    }

    /**
    * Updates objects data with the data provided from an associative array or another object.
    * Returns TRUE if the object was found by given index.
    *
    * @param integer $index
    * @param ApiObject | array $data
    * @return boolean
    */
    public function updateByIndex($index, $data)
    {
        $this->initialize();

        if ($internalObj = $this->getByIndex($index)) {
            $internalObj->updateData($data);
        }
    }

    /**
    * Returns TRUE if an object is found given another object or 
    * a primary key value. This method will check the server if the object 
    * is not found locally and the collection is not fully loaded at the moment
    *
    * @param mixed $object ApiObject or primery key
    * @return boolean
    */
    public function exists($object)
    {
        $this->initialize();

        $id = $this->obtainIdFrom($object);

        if ($this->isObjectLoaded($id)) {
            return true;
        } else if (!$this->isFullyLoaded()) {
            // Check server for this item
            if ($item = $this->_repository->get($id)) {
                $this->_objects[$item->getId()] = $item;

                return true;
            }
        }

        return false;
    }

    /**
    * Adds several new objects to be saved
    * Objects attached will NOT be accessible until the collection be saved.
    * If there were new objects from other additions they will be LOST
    *
    * @param array $objects
    * @return void
    */
    public function setObjects(array $objects)
    {
        $this->_dirtyObjects = array();

        foreach ($objects as $item) {
            if ($this->validateObject($item)) {
                $this->_dirtyObjects[] = $item;
            }
        }
    }

    /**
    * Returns objects loaded from given page
    * If not page is given, it uses current page
    *
    * @param integer $page
    * @return array 
    */
    public function getObjects($page = 0)
    {
        $this->initialize();

        return array_values($this->load($page));
    }

    /**
    * Removes an object from the collection and PERSIST de action on server side.
    *
    * @param mixed $object ApiObject or primery key
    * @param boolean $persist If TRUE the action is persisted on server side
    * @return void
    */
    public function remove($object, $persist = false)
    {
        $this->initialize();

        if ($this->exists($object)) {
            $id = $this->obtainIdFrom($object);
            $object = $this->get($id);

            if ($persist) {
                $object->remove();
            }

            unset($this->_objects[$id]);
            // Clear invalid page mappings
            $this->_pages = array();

            return $object;
        }

        return null;
    }

    /**
    * Removes an object from the collection and PERSIST the action on server side.
    * Returns removed object.
    *
    * @param integer $index
    * @param boolean $persist If TRUE the action is persisted on server side
    * @return ApiObject | null
    */
    public function removeByIndex($index, $persist = false)
    {
        $this->initialize();

        return $this->remove($this->getByIndex($index), $persist);
    }

    /**
    * Saves all new objects attached to the collection or modified
    * It clears the collection status and forces everything to be reloaded (when needed).
    *
    * @return void
    */
    public function save()
    {
        $this->initialize();

        $modifiedObjects = array();

        // Check for modified objects inside the collection
        foreach ($this->_objects as $object) {
            if ($object->isModified()) {
                $modifiedObjects[] = $object;
            }
        }

        $modifiedObjects = array_merge($modifiedObjects, $this->_dirtyObjects);

        $this->_repository->save($modifiedObjects);

        // If new were submitted to be saved, clear cached data (probably invalid now)
        if (!empty($this->_dirtyObjects)) {
            $this->clear();
            $this->_dirtyObjects = array();
        }
    }

    /**
    * Methods to satisfy the Countable Interface implementation
    */

    public function count() 
    {
        $this->initialize();

        return $this->_total ? $this->_total : count($this->_objects);
    }

    /**
    * Methods to satisfy the IteratorAggregate Interface implementation
    */

    public function getIterator()
    {
        return new ApiObjectIterator($this);
    }

    /**
    * Methods to satisfy the ArrayAccess Interface implementation
    * All ArrayAccess methods uses numeric index as parameter ($offset) 
    */

    public function offsetExists($offset)
    {
        return $this->validateIndex($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getByIndex($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->updateByIndex($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->removeByIndex($offset);
    }

    /**
    * Methods redefined from the AbstractPaginator
    */

    public function setCurrentPage($page)
    {
        $this->initialize();
        parent::setCurrentPage($page);
    }

    public function hasNextPage()
    {
        $this->initialize();
        return parent::hasNextPage();
    }
}