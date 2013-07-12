<?php

namespace Mr\Api\Http\Adapter;

use Mr\Api\ClientAdapterInterface;


class BaseAdapter extends \HTTP_Request2_Adapter implements ClientAdapterInterface
{
    /**
    * Returns a list of not allowed methods
    *
    * @return array
    */
    public function getDisallowedMethods()
    {
        return array();
    }

    /**
    * {@inheritdoc }
    */
    public function sendRequest(\HTTP_Request2 $request)
    {
        if (array_key_exists($request->getMethod(), array_flip($this->getDisallowedMethods()))) {
            throw new NotAllowedMethodTypeException();
        }

        return parent::sendRequest($request);
    }
}