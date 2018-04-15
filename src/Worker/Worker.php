<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 16:45
 */

namespace Worker;

use EventHandler\ResponseState;
use Helpers\ResponseHelper;
use Logger\Logger;
use RMQClient\RMQSender;
use Thread;

class Worker extends Thread
{
    private $_timeToWait;
    private $_sender;
    private $_logger;

    /**
     * Worker constructor.
     * @param RMQSender $sender
     * @param Logger $logger
     */
    public function __construct(RMQSender $sender, Logger $logger)
    {
        $this->_timeToWait = 2/*mt_rand(1, 10)*/;
        $this->_sender = $sender;
        $this->_logger = $logger;
    }

    public function run()
    {
        try {

            if (isset($this->_timeToWait)) {

                sleep($this->_timeToWait);
            }

        } catch (\Exception $e) {

            $this->_logger->error(sprintf("An error occurred while requesting InstagramApi : %s", $e->getMessage()));

            $this->kill();
        }
    }
}
