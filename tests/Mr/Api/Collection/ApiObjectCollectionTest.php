<?php 

namespace MrTest\Api\Collection;

// Client
use Mr\Api\Http\Client;
use Mr\Api\Http\Response;
use Mr\Api\Http\Adapter\MockAdapter;

// Repository
use Mr\Api\Repository\ApiRepository;
use Mr\Api\Repository\ChannelRepository;
use Mr\Api\Repository\MediaRepository;

// Collection
use Mr\Api\Collection\ApiObjectCollection;
use Mr\Api\Collection\ApiObjectIterator;

// Exception
use Mr\Exception\InvalidRepositoryException;

class ApiObjectCollectionMock extends ApiObjectCollection
{
    public function isInitialized()
    {
        return $this->_isInitialized;
    }

    public function getRepository()
    {
        return $this->_repository;
    }

    public function isObjectLoadedMockProperty($id)
    {
        return $this->isObjectLoaded($id);
    }

    public function isPageLoadedMockProperty($page)
    {
        return $this->isPageLoaded($page);
    }

    public function isFullyLoadedMockProperty()
    {
        return $this->isFullyLoaded();
    }

    public function isIndexLoadedMockProperty($index)
    {
        return $this->isIndexLoaded($index);
    }

    public function isAnyObjectLoaded()
    {
        return !empty($this->_objects) || !empty($this->_pages);
    }

    public function getMetadataMockProperty()
    {
        return $this->getMetadata();
    }

    public function getDirtyObjects()
    {
        return $this->_dirtyObjects;
    }

    public function getIterator()
    {
        return new MockIterator($this);
    }
};

class MockIterator extends ApiObjectIterator
{
    public function getCollectionMockProperty()
    {
        return $this->_collection;
    }

    public function getObjectsMockProperty()
    {
        return $this->_objects;
    }

    public function getCurrentIndexMockProperty()
    {
        return $this->_index;
    }
}

class ApiObjectCollectionTest extends \PHPUnit_Framework_TestCase
{
    const MODEL_NAMESPACE = 'Mr\\Api\\Model\\';
    const REPOSITORY_NAMESPACE = 'Mr\\Api\\Model\\';

    protected $page1ObjectsData = array(
        'status' => ApiRepository::STATUS_OK,
        'meta' => array(
            'total' => 3,
            'page' => 1,
            'pages' => 2,
            'limit' => 2
        ),
        'objects' => array(
             array(
                'id' => 1,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 1'
            ),
            array(
                'id' => 2,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 2'
            )
        )
    );

    protected $page2ObjectsData = array(
        'status' => ApiRepository::STATUS_OK,
        'meta' => array(
            'total' => 3,
            'page' => 2,
            'pages' => 2,
            'limit' => 2
        ),
        'objects' => array(
            array(
                'id' => 3,
                'url' => 'http://site.channel.com',
                'name' => 'Channel 3'
            )
        )
    );

    protected $_collection;
    protected $_repository;
    protected $_client;

    public function __construct()
    {
        $this->_client = new Client('anyhost', 'anyusername', 'anypassword');
        $this->_clientMockAdapter = new MockAdapter(); 
        $this->_client->setAdapter($this->_clientMockAdapter);
        $this->_repository = new ChannelRepository($this->_client);
    }

    private function addMockResponses()
    {
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->page1ObjectsData));
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode($this->page2ObjectsData));
        $this->_clientMockAdapter->addExceptionReponse();
    }

    public function setUp()
    {
        $this->addMockResponses();
        $this->_collection = new ApiObjectCollectionMock($this->_repository);
    }

    public function testRepositoryReference()
    {
        $this->assertEquals($this->_repository, $this->_collection->getRepository());
    }

    public function testNotInitialized()
    {
        // At this point collection should not be initilized yet
        $this->assertFalse($this->_collection->isInitialized());
        $this->assertFalse($this->_collection->isObjectLoadedMockProperty(1));
        $this->assertFalse($this->_collection->isPageLoadedMockProperty(1));
        $this->assertFalse($this->_collection->isIndexLoadedMockProperty(0));
        $this->assertFalse($this->_collection->isFullyLoadedMockProperty());
    }

    public function testInitialData()
    {
        $this->assertEquals($this->_collection->getCurrentPage(), 1);
        $this->assertFalse($this->_collection->isAnyObjectLoaded());
    }

    public function testFirstPageLoad()
    {
        $metadataData = $this->page1ObjectsData['meta'];
        $objects = $this->_collection->getObjects();
        $firstObject = $objects[0];

        $this->assertTrue($this->_collection->isInitialized());
        $this->assertTrue($this->_collection->isObjectLoadedMockProperty($firstObject->getId()));
        $this->assertTrue($this->_collection->isPageLoadedMockProperty(1));
        $this->assertTrue($this->_collection->isIndexLoadedMockProperty(0));
        $this->assertTrue($this->_collection->isIndexLoadedMockProperty(1));
        $this->assertCount(3, $this->_collection);
        // Not fully loaded yet
        $this->assertFalse($this->_collection->isFullyLoadedMockProperty());

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $objects);
        // Check metadata
        $collectionMetadata = $this->_collection->getMetadataMockProperty();
        ksort($metadataData);
        ksort($collectionMetadata);
        $this->assertEquals($metadataData, $collectionMetadata);
        // Check object count per page, it should be same as page limit
        $this->assertEquals(count($objects), $metadataData['limit']);
        // Check entity returned type
        $this->assertInstanceOf(self::MODEL_NAMESPACE . 'Channel', $objects[0]);

        $objectsSamePage = $this->_collection->getObjects();
        // Check if the returned objects are always the same if the page remains
        $this->assertEquals($objects, $objectsSamePage);

        // Object 3 not loaded
        $this->assertFalse($this->_collection->isObjectLoadedMockProperty(3));
        // Page 2 not loaded
        $this->assertFalse($this->_collection->isPageLoadedMockProperty(2));
    }

    public function testObjectsAccesibility()
    {
        $objects = $this->_collection->getObjects();
        $firstObject = $objects[0];

        // Check existence by object entity
        $this->assertTrue($this->_collection->exists($firstObject));
        // Check existence by object entity
        $this->assertTrue($this->_collection->exists($firstObject->getId()));

        $returnedObject = $this->_collection->get($firstObject->getId());
        $this->assertEquals($returnedObject, $firstObject);

        $returnedObject = $this->_collection->getByIndex(0);
        $this->assertEquals($returnedObject, $firstObject);
    }

    public function testPaginator()
    {
        $this->_collection->setCurrentPage(2);
        $this->assertEquals(2, $this->_collection->getCurrentPage());
        $this->assertTrue($this->_collection->hasPreviousPage());
        $this->assertFalse($this->_collection->hasNextPage());

        $this->_collection->setCurrentPage(1);
        $this->assertEquals(1, $this->_collection->getCurrentPage());
        $this->assertFalse($this->_collection->hasPreviousPage());
        $this->assertTrue($this->_collection->hasNextPage());

        $this->_collection->increasePage();
        $this->assertEquals(2, $this->_collection->getCurrentPage());

        $this->_collection->decreasePage();
        $this->assertEquals(1, $this->_collection->getCurrentPage());
    }

    public function testSecondPageLoad()
    {
        $metadataData = $this->page1ObjectsData['meta'];
        $this->_collection->getObjects();
        // Move to page 2
        $this->_collection->increasePage();
        $objects = $this->_collection->getObjects();
        $thirdObject = $objects[0];

        // Is still initialized
        $this->assertTrue($this->_collection->isInitialized());
        $this->assertTrue($this->_collection->isObjectLoadedMockProperty($thirdObject->getId()));
        $this->assertTrue($this->_collection->isPageLoadedMockProperty(2));
        $this->assertTrue($this->_collection->isIndexLoadedMockProperty(2));
        // Now should be fully loaded
        $this->assertTrue($this->_collection->isFullyLoadedMockProperty());
        $this->assertEquals(3, $this->_collection->count());

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $objects);
        // Check object count, should be the same as items left in data -> 1 (for second page)
        $this->assertCount(1, $objects);
        // Check entity returned type again
        $this->assertInstanceOf(self::MODEL_NAMESPACE . 'Channel', $objects[0]);

        $objectsSamePage = $this->_collection->getObjects();
        // Check if the returned objects are always the same if the page remains
        $this->assertEquals($objects, $objectsSamePage);
    }

    public function testClearCollection()
    {
        $this->_collection->getObjects();
        // Clear collection data
        $this->_collection->clear();

        // Is still initialized
        $this->assertTrue($this->_collection->isInitialized());
        $this->assertFalse($this->_collection->isObjectLoadedMockProperty(1));
        $this->assertFalse($this->_collection->isPageLoadedMockProperty(1));
        $this->assertFalse($this->_collection->isIndexLoadedMockProperty(0));
        $this->assertFalse($this->_collection->isAnyObjectLoaded());
        $this->assertEquals(3, $this->_collection->count());

        // Check metadata
        $metadataData = $this->page1ObjectsData['meta'];
        $collectionMetadata = $this->_collection->getMetadataMockProperty();
        ksort($metadataData);
        ksort($collectionMetadata);
        $this->assertEquals($metadataData, $collectionMetadata);

        // Objects can be reloaded (from second mock response)
        $this->_collection->setCurrentPage(2);
        $this->_collection->getObjects();
        $this->assertTrue($this->_collection->isObjectLoadedMockProperty(3));
        $this->assertTrue($this->_collection->isPageLoadedMockProperty(2));
    }

    public function testCollectionObjectChanges()
    {
        // Updates object with ID 1
        $this->_collection->update(array('name' => 'Updated'), 1);
        $object = $this->_collection->get(1);
        $this->assertEquals('Updated', $object->name);
        // Only updated object page should be loaded
        $this->assertFalse($this->_collection->isFullyLoadedMockProperty());

        // Updates all objects
        $this->_collection->update(array('name' => 'Updated'));
        foreach ($this->_collection as $object) {
            $this->assertEquals('Updated', $object->name);
            $this->assertTrue($object->isModified());
        }
        // Only updated object page should be loaded
        $this->assertTrue($this->_collection->isFullyLoadedMockProperty());

        // Empty response to satisfy save request
        $this->_clientMockAdapter->clear();
        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, '', json_encode(array(
            'status' => 'ok'
        )));
        $this->_collection->save();
        // All objects should be saved
        foreach ($this->_collection as $object) {
            $this->assertFalse($object->isNew());
            $this->assertFalse($object->isModified());
        }

        $dataObject = $this->page1ObjectsData['objects'][0];

        $this->_clientMockAdapter->addResponseBy(Response::STATUS_OK, 'api/channel', json_encode(array(
            'status' => 'ok',
            'object' => $dataObject
        )));

        // Adding new object
        $this->_collection->add($this->_repository->create());
        $this->assertCount(1, $this->_collection->getDirtyObjects());
        $this->_collection->save();
        $this->assertCount(0, $this->_collection->getDirtyObjects());
        // After saving a new object collection needs to be cleared
        $this->assertFalse($this->_collection->isAnyObjectLoaded());

        // Adds initial objects mock data for collection reload
        $this->_clientMockAdapter->clear();
        $this->addMockResponses();
        // Forces full reload
        $this->_collection->toArray();
        $this->assertTrue($this->_collection->isFullyLoadedMockProperty());
    }

    public function testToArrayForFullLoad()
    {
        $objArray = $this->_collection->toArray();

        // Objects were returned
        $this->assertCount(3, $objArray);
        $this->assertTrue($this->_collection->isFullyLoadedMockProperty());
    }

    /**
     * @expectedException Mr\Exception\InvalidRepositoryException
     */
    public function testRepositoryModelMatch()
    {
        $collection = new ApiObjectCollection(new MediaRepository($this->_client));
        $collection->add($this->_repository->create());
    }

    public function testIterator()
    {
        $iterator = $this->_collection->getIterator();

        $this->assertInstanceOf("Mr\Api\Collection\ApiObjectCollection", $iterator->getCollectionMockProperty());
        $this->assertCount(0, $iterator->getObjectsMockProperty());
        $this->assertEquals(0, $iterator->getCurrentIndexMockProperty());

        $iterator->rewind();
        // Loads first two objects from first page
        $this->assertCount(2, $iterator->getObjectsMockProperty());
        $this->assertEquals(0, $iterator->getCurrentIndexMockProperty());

        $object = $iterator->current();
        $this->assertInstanceOf("Mr\Api\Model\Channel", $object);
        $this->assertEquals(0, $iterator->key());
        $this->assertTrue($iterator->valid());
        $this->assertEquals(1, $object->getId());

        $iterator->next();
        $object = $iterator->current();
        $this->assertEquals(1, $iterator->key());
        $this->assertTrue($iterator->valid());
        $this->assertEquals(2, $object->getId());
        $this->assertCount(2, $iterator->getObjectsMockProperty());
        $this->assertEquals(1, $iterator->getCurrentIndexMockProperty());

        $iterator->next();
        $object = $iterator->current();
        $this->assertEquals(2, $iterator->key());
        $this->assertTrue($iterator->valid());
        $this->assertEquals(3, $object->getId());
        $this->assertCount(3, $iterator->getObjectsMockProperty());
        $this->assertEquals(2, $iterator->getCurrentIndexMockProperty());

        $iterator->next();
        $this->assertFalse($iterator->valid());

        // Deletes collection and objects references after becomes invalid (finish iteration)
        $this->assertNull($iterator->getCollectionMockProperty());
        $this->assertNull($iterator->getObjectsMockProperty());
    }

    public function testArrayAccess()
    {
        $objects = $this->_collection->getObjects();
        $firstObject = $objects[0];

        $this->assertTrue(isset($this->_collection[0]));
        $this->assertEquals($this->_collection[0], $firstObject);
        $this->assertFalse($this->_collection->isFullyLoadedMockProperty());

        // Updates object by index using data
        $this->_collection[0] = array('name' => 'Updated');
        $object = $this->_collection->get(1);
        $this->assertEquals('Updated', $object->name);

        // Updates object by index using another object
        $secondObject = $this->_collection[1];
        $this->_collection[0] = $secondObject;
        $object = $this->_collection->getByIndex(0); //$firstObject
        $this->assertEquals($secondObject->name, $object->name);

        // Force full load
        $this->_collection->toArray();
        unset($this->_collection[0]);
        // After remove an object, page mappings should be cleared
        $this->assertFalse($this->_collection->isPageLoadedMockProperty(1));
    }
}