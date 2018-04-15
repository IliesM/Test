<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:09
 */

namespace EventHandler;

class EventHandler
{

    public static function parseEvent($payload)
    {
        $event = json_decode($payload, true);

        if (isset($event['eventType']))
        {
            $handler = EventType::getEvent($event['eventType']);

            self::$handler($event['data']);
        }
    }

    public function loadEyesAccounts($data)
    {
        $GLOBALS['eyesAccounts'] = $data['accounts'];
        //var_dump($GLOBALS['eyesAccounts']);
    }

    public function loadUserAccounts($data)
    {
        $GLOBALS['userAccounts'] = $data['accounts'];
        //var_dump($GLOBALS['userAccounts']);
    }

    public function loadEyesMessages($data)
    {
        $GLOBALS['eyesMessages'] = $data['messages'];
        //var_dump($GLOBALS['eyesMessages']);
    }

    public function start($data)
    {
        $GLOBALS['messagingEngine']->startMessaging();
    }
}