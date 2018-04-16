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
        $response["data"]['user'] = $payload['UserID'];
        $response["data"]['message'] = $payload['message'];
        $response["data"]['eyesAccount'] = $payload['message'];

        var_dump($response);
        return json_encode($response);
    }
}