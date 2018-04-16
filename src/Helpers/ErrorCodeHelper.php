<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ilies
 * Date: 16/04/2018
 * Time: 14:47
 */

namespace Helpers;


class ErrorCodeHelper
{
    const BAD_INITIALIZATION = ['code' => 1000, 'message' => 'Accounts/Messages/Users are not well initialized'];
    const ERROR_RETRIEVING = ['code' => 1001, 'message' => 'Failed at retrieving message from queue %s : %s'];
    const ERROR_SENDING = ['code' => 1002, 'message' => 'Failed at sending message to queue %s'];
    const CONNECTION_ERROR = ['code' => 1003, 'message' => 'Error while connecting AMPQ client : %s'];
}