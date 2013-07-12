<?php

namespace Mr\Api\Model;

interface ApiObjectCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
    public function getModel();

    public function setCurrentPage($page);

    public function getCurrentPage();

    public function increasePage();

    public function decreasePage();

    public function hasNextPage();

    public function hasPreviousPage();

    public function toArray();

    public function clear();

    public function add(ApiObject $object);

    public function get($id);

    public function setObjects(array $objects);

    public function remove($object);

    public function exists($object);

    public function save();
}