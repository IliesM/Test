<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:34
 */

use Configuration\Configuration;
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

$sender = new RMQSender($config);
$receiver = new RMQReceiver($config);

//$sender->send();
$receiver->receive();
