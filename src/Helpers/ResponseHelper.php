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
    public static function createTaskResponse($state, $payload)
    {
        $response = [];

        $response["state"] = $state;
        $response["data"]['user'] = $payload['UserID'];
        $response["data"]['message'] = $payload['message'];
        $response["data"]['eyesAccount'] = $payload['eyesAccount'];

        return json_encode($response);
    }

    public static function createErrorResponse($error, $payload = null)
    {
        $response = [];

        $response["errorCode"] = $error['code'];
        $response["errorMsg"] = $error['message'];
        $response["data"] = $payload;

        return json_encode($response);
    }
}