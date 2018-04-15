<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 16:45
 */

namespace Worker;

use Thread;

class Worker extends Thread
{
    private $_timeToWait;

    /**
     * Worker constructor.
     * @param $timeToWait
     */
    public function __construct()
    {
        $this->_timeToWait = mt_rand(1, 10);
    }

    public function run()
    {
        if (isset($this->_timeToWait)) {

            printf("TID : %s sleep for %d secondes\n", $this->getThreadId(), $this->_timeToWait);
            sleep($this->_timeToWait);
            printf("TID : %s done\n", $this->getThreadId());
        }
    }
}