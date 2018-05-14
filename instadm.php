<?php

require __DIR__.'/vendor/autoload.php';

$ig = new \InstagramAPI\Instagram(false, false);
$task = json_decode($argv[1], true);

$loginState = $ig->login($task['Username'], $task['Password']);
$loginState = json_decode($loginState, true);

if ($loginState["status"] == "ok") {
    var_dump("login state ".$loginState);

    foreach ($task["userAccounts"] as $userAccount) {

        //var_dump(print_r($userAccount, 1));
        $ig->direct->sendText(
            [
                'users' => [
                    $userAccount['UserID']
            ]],
            $userAccount["message"]);
        var_dump("message from ".$task['Username']." sent to ".$userAccount['Name']);
    }
}
else
    var_dump("login state ".print_r($loginState, 1));


$logoutState = $ig->logout();
$logoutState = json_decode($logoutState, true)["status"];
var_dump("logout state ".$logoutState);
