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

class MyWorker extends \Worker
{
    private $_timeToWait;
    private $_sender;
    private $_logger;
    private $_complete;
    private $_success;

    /**
     * Task constructor.
     * @param RMQSender $sender
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->_timeToWait = mt_rand(1, 10);
        $this->_logger = $logger;
        $this->_complete = false;
    }

    public function run()
    {
        try {

            if (isset($this->_timeToWait)) {

                sleep($this->_timeToWait);
                $this->_complete = true;
                   $this->_success = true;

            }

        } catch (\Exception $e) {

            $this->_logger->error(sprintf("An error occurred while requesting InstagramApi : %s", $e->getMessage()));
            echo "An error occurred while requesting InstagramApi".PHP_EOL;
            $this->_success = false;
            //TODO Disconnect the account
        }
    }

    public function isComplete() {
        return $this->_complete;
    }

    public function isSuccess()
    {
        return $this->_success;
    }
}
