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

    /**
     * Parse event and execute the right function
     * @param $payload
     */
    public static function parseEvent($payload)
    {
        $event = json_decode($payload, true);

        if (isset($event['eventType']))
        {
            $handler = EventType::getEvent($event['eventType']);

            self::$handler($event['data']);
        }
    }

    /**
     * Load eyes accounts
     * @param $data
     */
    public function loadEyesAccounts($data)
    {
        $GLOBALS['eyesAccounts'] = $data['accounts'];
    }

    /**
     * Load user accounts
     * @param $data
     */
    public function loadUserAccounts($data)
    {
        $GLOBALS['userAccounts'] = $data['accounts'];
    }

    /**
     * Load eyes messages
     * @param $data
     */
    public function loadEyesMessages($data)
    {
        $GLOBALS['eyesMessages'] = $data['messages'];
    }

    /**
     * Fire Start event that launch StartMessaging()
     * @param $data
     */
    public function start($data)
    {
        $GLOBALS['isStopped'] = false;
        echo 'Application has been started'.PHP_EOL;
        $GLOBALS['messagingEngine']->startMessaging();
    }

    public function stop($data)
    {
        echo 'Application has been paused'.PHP_EOL;
        $GLOBALS['isStopped'] = true;
    }
}