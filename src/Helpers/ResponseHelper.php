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
    /**
     * Create a response with task state and related task in payload
     * @param $state
     * @param $payload
     * @return string
     */
    public static function createTaskResponse($state, $payload)
    {
        $response = [];

        $response["state"] = $state;
//        $response["data"]['user'] = $payload['UserID'];
//        $response["data"]['message'] = $payload['message'];
//        $response["data"]['eyesAccount'] = $payload['eyesAccount'];

        return json_encode($response);
    }

    /**
     * Create a error response with the error's message and code
     * @param $error
     * @param null $payload
     * @return string
     */
    public static function createErrorResponse($error, $payload = null)
    {
        $response = [];

        $response["errorCode"] = $error['code'];
        $response["errorMsg"] = $error['message'];
        $response["data"] = $payload;

        return json_encode($response);
    }
}