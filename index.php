<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:34
 */

use Configuration\Configuration;
use EventHandler\EventHandler;
use Logger\Logger;
use MessagingEngine\MessagingEngine;
use RMQClient\RMQReceiver;
use RMQClient\RMQSender;

require __DIR__ . '/vendor/autoload.php';


$config = new Configuration(__DIR__.'/config/config.json');

$config->loadConfiguration();

$logger = new Logger($config);

$GLOBALS['logger'] = $logger;

$GLOBALS['isStopped'] = false;

$GLOBALS['messagingEngine'] = new MessagingEngine($config);

$eyesAccountsPayload = file_get_contents("example_payload/loadEyesAccountsPayload.json");
$eyesMessagesPayload = file_get_contents("example_payload/loadEyesMessagePayload.json");
$userAccountsPayload = file_get_contents("example_payload/loadUserAccountsPayload.json");
$startPayload = file_get_contents("example_payload/startPayload.json");

EventHandler::parseEvent($eyesAccountsPayload);
EventHandler::parseEvent($eyesMessagesPayload);
EventHandler::parseEvent($userAccountsPayload);
EventHandler::parseEvent($startPayload);

//$receiver = new RMQReceiver($config);

//$receiver->receive();
