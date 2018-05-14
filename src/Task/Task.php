<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 16:45
 */

namespace Task;

use InstagramAPI\Instagram;
use Logger\Logger;

class Task extends \Thread
{
    private $_timeToWait;
    private $_logger;
    private $_success;
    private $_task;

    /**
     * Task constructor.
     * @param Logger $logger
     * @param $task
     */
    public function __construct(Logger $logger, $task)
    {
        $this->_timeToWait = 0;
        //$this->_timeToWait = mt_rand(900, 1200);
        $this->_logger = $logger;
        $this->_task = $task;
    }

    /**
     * Run Instagram's Api call
     */
    public function run()
    {
        try {

            if (isset($this->_timeToWait)) {

                $task = json_encode($this->_task);

                //sleep($this->_timeToWait);
                system('php instadm.php '."'".$task."'");
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
