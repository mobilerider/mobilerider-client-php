<?php

namespace Mr\Api\Http\Adapter;

use Mr\Api\AbstractClient;
use Mr\Api\Http\Adapter\BaseAdapter;


class ReadOnlyAdapter extends BaseAdapter
{
    /**
    * {@inheritdoc }
    */
    public function getDisallowedMethods()
    {
        return array(
            AbstractClient::METHOD_POST,
            AbstractClient::METHOD_PUT,
            AbstractClient::METHOD_DELETE
        );
    }
}