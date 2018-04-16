<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 15:06
 */

namespace EventHandler;


class EventType
{
    /**
     * Return the event's function
     * @param $eventType
     * @return mixed
     */
    static function getEvent($eventType)
    {
        $events = [
            1 => 'loadEyesAccounts',
            2 => 'loadUserAccounts',
            3 => 'loadEyesMessages',
            4 => 'start',
            5 => 'stop',
            6 => 'forceUpdate'
        ];

        return ($events[$eventType]);
    }
}