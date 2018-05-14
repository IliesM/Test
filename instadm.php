<?php

use Model\TaskModel;

require __DIR__.'/vendor/autoload.php';

$ig = new \InstagramAPI\Instagram(false, false);

$task = new TaskModel(json_decode($argv[1], true));

$loginState = $ig->login($task->getEyesAccountUsername(), $task->getEyesAccountPassword());
$loginState = json_decode($loginState, true);

if ($loginState["status"] == "ok") {

    foreach ($task->getUserAccounts() as $userAccount) {

        $ig->direct->sendText(
            [
                'users' => [
                    $userAccount['UserID']
            ]],
            $userAccount["message"]);
        var_dump("message from ".$task->getEyesAccountUsername()." sent to ".$userAccount['Name']);
    }
}
else
    var_dump("login state ".print_r($loginState, 1));


$logoutState = $ig->logout();
$logoutState = json_decode($logoutState, true)["status"];
var_dump("logout state ".$logoutState);
