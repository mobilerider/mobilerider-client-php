<?php

namespace MrTest\Api\Query;

use Mr\Api\Query\QuerySet;

/**
 * Class QuerySetTest
 */
class QuerySetTest extends \PHPUnit_Framework_TestCase
{
    public function testClassArguments()
    {
        $filters = array(array(
            'id__gt' => 5
        ));

        $fields = array('id', 'name');

        $q = new QuerySet($filters, $fields);

        $this->assertEquals(array(
            'fields' => $fields,
            'filters' => $filters
        ), $q->toArray());
    }

    public function testSelectFields()
    {
        $q = new QuerySet();

        $q->select('id');
        $this->assertEquals(array('fields' => array('id')), $q->toArray());

        $q->select(array('name', 'price'));
        $this->assertEquals(array('fields' => array('id', 'name', 'price')), $q->toArray());
    }

    public function testSimpleFilters()
    {
        $q = new QuerySet();

        $q->filter('name', 'contains', 'test');
        $this->assertEquals(array('filters' => array(
            array('name__contains' => 'test')
        )), $q->toArray());

        $q->filter('price', 'lt', 5);
        $this->assertEquals(array('filters' => array(
            array('name__contains' => 'test'),
            array('price__lt' => 5)
        )), $q->toArray());
    }

    public function testFiltersInSameArray()
    {
        $q = new QuerySet();

        $filters = array(
            'id__gt' => 5,
            'id__lt'=> 1
        );

        $q->filter($filters);

        $this->assertEquals(array('filters' => array($filter)), $q->toArray());
    }

    public function testOneFilterPassed()
    {
        $q = new QuerySet();
        $filter = array('name__contains' => 'test');
        $q->filter($filter);

        $this->assertEquals(array('filters' => array($filter)), $q->toArray());
    }

    public function testOneLevelOperators()
    {
        $q = new QuerySet();

        $filters = array(
            array('name__contains' => 'test'),
            'or',
            array('price__lt' => 5),
            'and',
            array(
                array('price__gt' => 1),
                'or',
                array('price__lt' => 5),
            )
        );

        $q->filter($filters);

        $this->assertEquals(array('filters' => $filters), $q->toArray());
    }

    public function testOperatorsAsKeys()
    {
        $q = new QuerySet();

        $filters = array(
            array('name__contains' => 'test'),
            'or' => array('price__lt' => 5),
            'and' => array(
                array('price__gt' => 1),
                'or' => array('price__lt' => 5),
            )
        );

        $q->filter($filters);

        $this->assertEquals(array('filters' => array(
            array('name__contains' => 'test'),
            'or',
            array('price__lt' => 5),
            'and',
            array(
                array('price__gt' => 1),
                'or',
                array('price__lt' => 5),
            ))
        ), $q->toArray());
    }

    public function testNestedFilters()
    {
        $q = new QuerySet();

        $filters = array(
            array('name__contains' => 'test'),
            array(
                array('price__lt' => 5),
                array('price__gt' => 1),
                array(
                    array('price__lt' => 5),
                    array('price__gt' => 1),
                )
            )
        );

        $q->filter($filters);

        $this->assertEquals(array('filters' => $filters), $q->toArray());
    }

    public function testMagicCallWithOneParam()
    {
        $q = new QuerySet();
        $q->name__contains('test');

        $this->assertEquals(array('filters' => array(array('name__contains' => 'test'))), $q->toArray());
    }

    public function testMagicCallWithTwoParams()
    {
        $q = new QuerySet();
        $q->contains('name', 'test');

        $this->assertEquals(array('filters' => array(array('name__contains' => 'test'))), $q->toArray());
    }

    /**
     * @expectedException \Mr\Exception\InvalidFiltersException
     */
    public function testInvalidFilterInsideInputArray()
    {
        $q = new QuerySet();
        $q->filter(array('name__not_valid_filter' => 'test'));
    }
}