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

        $response["eventType"] = $state;
        $response["data"] = $payload;
        $response["data"]["container"] = getenv("CONTAINER");

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