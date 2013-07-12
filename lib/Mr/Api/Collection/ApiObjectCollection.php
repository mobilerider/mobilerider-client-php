<?php 

namespace Mr\Api\Collection;

use Mr\Api\Repository\ApiRepository;
use Mr\Api\Model\ApiObject;

// Exceptions
use Mr\Exception\InvalidTypeException;

class ApiObjectCollection extends AbstractPaginator implements ApiObjectCollectionInterface
{
    protected $_isInitialized = false;

    protected $_objects = array();
    protected $_pages = array();
    protected $_dirtyObjects = array();

    protected $_limit;

    protected $_repository;

    public function __construct(ApiRepository $repository, $page = 1)
    {
        $this->_repository = $repository;

        if ($page > 1) {
            $this->setCurrentPage($page);
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
        return 0 <= $index && ($this->_total - 1) >= $index;
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
    protected function isItemLoaded($id)
    {
        return array_key_exists($id, $this->_objects);
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
        return array_key_exists($page, $this->_pages);
    }

    /**
    * Checks if all objects have been loaded already.
    * Returns TRUE all objects are loaded.
    *
    * @return boolean
    */
    protected function isFullyLoaded()
    {
        return count($this->_objects) == $this->_total;
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

            $this->_total = $metadata['total'];
            $this->_pageTotal = $metadata['pages'];
            $this->_limit = $metadata['limit'];

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
        $page = $page ? $page : $this->_page;

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

            $this->_pages[$this->_page] = array();

            foreach ($objects as $object) {
                if ($this->validateObject($object)) {
                    // Add object object by primery key
                    $this->_objects[$object->getId()] = $object;
                    // Add object object to its page
                    $this->_pages[$page][] = $object;
                }
            }
        }
    }

    /**
    * Loads all objects from all pages making iterative single page loads.
    *
    * @return void
    */
    protected function loadAll()
    {
        $this->setCurrentPage(1);

        while (!$this->isFullyLoaded()) {
            $this->load();
            $this->increasePage();
        }
    }

    protected function obtainIdFrom($object) 
    {
        return $id = is_object($object) && $this->validateObject($object) ? $object->getId() : $object;
    }

    public function getIndexOf($object)
    {
        $id = $this->obtainIdFrom($object);

        return array_search($this->getIds(), $id);
    }

    public function getPageOf($object)
    {
        return $this->getPageByIndex($this->getIndexOf($object));
    }

    public function getPageByIndex($index)
    {
        return floor($index / $this->_limit);
    }

     /**
    * Methods to satisfy the ApiObjectCollectionInterface Interface implementation
    */

    public function getModel()
    {
        return $this->_repository->getModel();
    }

    public function getIds()
    {
        return array_keys($this->_objects);
    }

    public function getByIndex($index)
    {
        if ($this->validateIndex($index)) {
            $ids = $this->getIds();

            // Check if the object is loaded, 
            // if NOT load the page that contains it
            if (!isset($ids[$index])) {
                $page = $this->getPageByIndex($index);
                $this->load($page);
                $ids = $this->getIds();
            }

            $id = $ids[$index];

            return $this->get($id);
        } 

        return null;
    }

    public function get($id)
    {
        $this->initialize();

        if ($this->exists($id)) {
            return $this->_objects[$id];
        }   

        return null;
    }

    public function toArray()
    {
        $this->initialize();

        $this->loadAll();

        return array_values($this->_objects);
    }

    public function clear()
    {
        $this->_objects = array();
        $this->_pages = array();
    }

    public function add(ApiObject $object)
    {
        $this->_dirtyObjects[] = $this->validateObject($object);
    }

    public function update(ApiObject $object)
    {
        $this->validateObject($object);

        if ($internalObj = $this->get($object->getId())) {
            $internalObj->updateData($object->getData());
        }
    }

    public function updateByIndex($index, ApiObject $object)
    {
        $this->validateObject($object);

        if ($internalObj = $this->getByIndex($index)) {
            $internalObj->updateData($object->getData());
        }
    }

    public function exists($object)
    {
        $id = $this->obtainIdFrom($object);

        if ($this->isItemLoaded($id)) {
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

    public function setObjects(array $objects)
    {
        $this->_dirtyObjects = array();

        foreach ($objects as $item) {
            if ($this->validateObject($item)) {
                $this->_dirtyObjects[] = $item;
            }
        }
    }

    public function getObjects($page = 0)
    {
        $page = $page ? $this->validatePage($page) : $this->_page;

        $this->load($page);

        return array_values($this->_pages[$page]);
    }

    public function remove($object)
    {
        if ($this->exists($object)) {
            $id = $this->obtainIdFrom($object);
            $object = $this->get($id);
            $object->remove();

            unset($this->_objects[$id]);
            // Clear invalid page mappings
            array_slice($this->_pages, $this->getPageOf($id));
        }
    }

    public function removeByIndex($index)
    {
        $this->remove($this->getByIndex($offset));
    }

    public function save()
    {
        $modifiedObjects = array();

        // Check for modified objects inside the collection
        foreach ($this->_objects as $object) {
            if ($object->isModified()) {
                $modifiedObjects[] = $object;
            }
        }

        $modifiedObjects = array_merge($modifiedObjects, $this->_dirtyObjects);

        $this->_repository->save($modifiedObjects);

        // If new objects has been saved, clear cached data
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
        return $this->_total;
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
        return $this->validateIndex($object);
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
}