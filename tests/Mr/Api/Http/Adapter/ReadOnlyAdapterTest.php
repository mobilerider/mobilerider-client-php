<?php

namespace MrTest\Api\Http\Adapter;

use Mr\Api\Http\Adapter\ReadOnlyAdapter;


class ReadOnlyAdapterTest extends \PHPUnit_Framework_TestCase {

    public function testGetDisallowedMethods() {
        $adapter = new ReadOnlyAdapter();

        $expected = array('POST', 'PUT', 'DELETE');
        sort($expected);

        $actual = $adapter->getDisallowedMethods();
        sort($actual);

        $this->assertEquals($expected, $actual);
    }
}