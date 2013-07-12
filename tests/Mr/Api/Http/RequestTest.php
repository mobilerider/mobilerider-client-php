<?php

namespace MrTest\Api\Http;

use Mr\Api\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase {

    public function testRequestGetSetField() {
        $request = new Request('/some/path');

        $request->X_SOME_FIELD = 'SOME-FIELD-VALUE';
        $this->assertEquals('SOME-FIELD-VALUE', $request->X_SOME_FIELD);

        $request->setParameter('X_SOME_OTHER_FIELD', 'SOME-OTHER-FIELD-VALUE');
        $this->assertEquals('SOME-OTHER-FIELD-VALUE', $request->X_SOME_OTHER_FIELD);

        $request->setParameter(array(
            'X_SOME_ANOTHER_FIELD' => 'SOME-ANOTHER-FIELD-VALUE',
            'X_SOME_YET_ANOTHER_FIELD' => 'SOME-YET-ANOTHER-FIELD-VALUE',
        ));
        $this->assertEquals('SOME-ANOTHER-FIELD-VALUE', $request->X_SOME_ANOTHER_FIELD);
        $this->assertEquals('SOME-YET-ANOTHER-FIELD-VALUE', $request->X_SOME_YET_ANOTHER_FIELD);

    }

}
