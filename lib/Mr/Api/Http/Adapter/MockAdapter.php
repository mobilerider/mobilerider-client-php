<?php

namespace Mr\Api\Http\Adapter;

use Mr\Api\ClientAdapterInterface;

class MockAdapter extends \HTTP_Request2_Adapter_Mock implements ClientAdapterInterface
{
    /**
    * Adds a mock response build from given parameters
    *
    * @param string $status Response status
    * @param string $url Url for specific request matching
    * @param string $content Response body
    * @return void
    */
    public function addResponseBy($status = Response::STATUS_OK, $url = '', $content = '')
    {
        $phrase = Response::getPhraseStatus($status);
        $response = "HTTP/1.1 {$status} {$phrase}\r\n Connection: close\r\n\r\n{$content}";
        
        $this->addResponse($response);
    }

    /**
    * Adds a response of type exception
    *
    * @return void
    */
    public function addExceptionReponse()
    {
        $adapter->addResponse(new \HTTP_Request2_Exception("Server Mock Response Exception!"));
    }
}