<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ilies
 * Date: 16/04/2018
 * Time: 14:47
 */

namespace Helpers;

/***
 * Regroups all of the error codes of the application
 * Class ErrorCodeHelper
 * @package Helpers
 */
class ErrorCodeHelper
{
    const BAD_INITIALIZATION = ['code' => 1000, 'message' => 'Accounts/Messages/Users are not well initialized'.PHP_EOL];
    const ERROR_RETRIEVING = ['code' => 1001, 'message' => 'Failed at retrieving message from queue %s : %s'.PHP_EOL];
    const ERROR_SENDING = ['code' => 1002, 'message' => 'Failed at sending message to queue %s'.PHP_EOL];
    const CONNECTION_ERROR = ['code' => 1003, 'message' => 'Error while connecting AMPQ client : %s'.PHP_EOL];
    const CONFIG_FILE_NOT_FOUND = ['code' => 1004, 'message' => 'Config/config.json file not found'.PHP_EOL];
    const CONFIG_FILE_ERROR = ['code' => 1005, 'message' => 'config.json error : %s'.PHP_EOL];
}