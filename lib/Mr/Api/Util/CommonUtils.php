<?php 

namespace Mr\Api\Util;

use Mr\Exception\JsonException;

class CommonUtils
{
    public static function decodeJson($json)
    {
        $data = json_decode($json);

        if (JSON_ERROR_NONE != ($jsonError = json_last_error())) {
            throw new JsonException($jsonError);
        }

        return $data;
    }

    public static function encodeJson($data)
    {
        $json = json_encode($data);

        if (JSON_ERROR_NONE != ($jsonError = json_last_error())) {
            throw new JsonException($jsonError);
        }

        return $json;
    }
}