<?php

namespace Helpers;

/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 15/04/18
 * Time: 21:21
 */

class ResponseHelper
{
    public static function createResponse($state, $payload)
    {
        $response = [];

        $response["state"] = $state;
        $response["data"] = $payload;

        return $response;
    }
}