<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:09
 */

namespace EventHandler;

use Helpers\ResponseHelper;

class EventHandler
{

    /**
     * Parse event and execute the right function
     * @param $payload
     * @return bool
     */
    public static function parseEvent($payload)
    {
        $event = json_decode($payload, true);

        if (isset($event['eventType']))
        {
            if ($event['eventType'] == 5) {
                $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::NotReady, null));
                return (self::stop());
            }
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

    public static function stop()
    {
        @system("rm pids.log");
        @system("ps -ef | grep instadm | grep -v grep | awk '{print $2}' >> pids.log");
        $pids = explode("\n", file_get_contents("pids.log"));

        foreach ($pids as $pid)
        {
            @system("kill ".$pid);
        }

        $GLOBALS['sender']->purge();
        sleep(1);
        echo 'Application has been stopped'.PHP_EOL;
        $GLOBALS['sender']->send(ResponseHelper::createTaskResponse(ResponseState::Ready, null));
        return 1;
    }
}