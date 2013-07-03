<?php

namespace Mr\Api\Http\Adapter;

use Mr\Api\ClientAdapterInterface;

class MockAdapter extends \HTTP_Request2_Adapter_Mock implements ClientAdapterInterface
{
    public function addResponseWith($status = Response::STATUS_OK, $url = '', $content = '')
    {
        $phrase = Response::getPhraseStatus($status);
        $response = "HTTP/1.1 {$status} {$phrase}\r\n Connection: close\r\n\r\n{$content}";
        
        $this->addResponse($response);
    }

    public function addExceptionReponse()
    {
        $adapter->addResponse(new \HTTP_Request2_Exception("Server Mock Response Exception!"));
    }
}