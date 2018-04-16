<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 16:45
 */

namespace Task;

use Logger\Logger;

class Task extends \Worker
{
    private $_timeToWait;
    private $_logger;
    private $_success;

    /**
     * Task constructor.
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->_timeToWait = mt_rand(1, 10);
        $this->_logger = $logger;
    }

    /**
     * Run Instagram's Api call
     */
    public function run()
    {
        try {

            if (isset($this->_timeToWait)) {

                sleep($this->_timeToWait * 50);
                $this->_success = true;

            }

        } catch (\Exception $e) {

            $this->_logger->error(sprintf("An error occurred while requesting InstagramApi : %s", $e->getMessage()));
            $this->_success = false;
            //TODO Disconnect the account
        }
    }

    /**
     * Return thread final state
     * @return mixed
     */
    public function isSuccess()
    {
        return $this->_success;
    }
}
