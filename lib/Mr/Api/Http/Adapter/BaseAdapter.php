<?php

namespace Mr\Api\Http\Adapter;

class BaseAdapter extends \HTTP_Request2_Adapter implements ClientAdapterInterface
{
	public function getDisallowedMethods()
	{
		return array();
	}

	public function sendRequest(\HTTP_Request2 $request)
	{
		if (array_key_exists($request->getMethod(), array_flip($this->getDisallowedMethods()))) {
			throw new NotAllowedMethodTypeException();
		}

		parent::sendRequest($request);
	}
}