<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:56
 */

namespace MessagingEngine;

use Configuration\Configuration;
use EventHandler\ResponseState;
use Helpers\ErrorCodeHelper;
use Helpers\ResponseHelper;
use InstagramAPI\Instagram;
use Logger\Logger;
use function MongoDB\BSON\toJSON;
use RMQClient\RMQSender;
use Task\Task;

class MessagingEngine
{

    private $_userAccounts;
    private $_eyesAccounts;
    private $_eyesMessages;
    /**
     * @var Logger
     */
    private $_logger;
    /**
     * @var Task[]
     */
    private $_workers;
    /**
     * @var RMQSender
     */
    private $_sender;

    /**
     * MessagingEngine constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->_userAccounts = null;
        $this->_eyesAccounts = null;
        $this->_eyesMessages = null;
        $this->_logger = $GLOBALS['logger'];
        //$this->_sender = new RMQSender($configuration);
        $this->_workers = [];
    }

    /**
     * Load needed data received from C# application
     */
    public function loadData()
    {
        $this->_userAccounts = ((isset($GLOBALS['userAccounts']))) ? $GLOBALS['userAccounts']: null;
        $this->_eyesAccounts = ((isset($GLOBALS['eyesAccounts']))) ? $GLOBALS['eyesAccounts']: null;
        $this->_eyesMessages = ((isset($GLOBALS['eyesMessages']))) ? $GLOBALS['eyesMessages']: null;
    }

    /**
     * Load, prepare and process tasks in threads
     */
    public function startMessaging()
    {
        $this->loadData();

        if ($this->_userAccounts && $this->_eyesAccounts && $this->_eyesMessages) {

            $tasks = $this->prepareTasks();
            //TODO Login
            foreach ($tasks as $task) {

                if (!$GLOBALS['isStopped']) {
                    $this->_workers[] = new Task($this->_logger, $task);

                    //$response = ResponseHelper::createTaskResponse(ResponseState::Running, $tasks[$i]);
                    //$this->_sender->send($response);
                }
            }

            foreach ($this->_workers as $worker) {
                $worker->start();
            }

            foreach ($this->_workers as $worker) {

                if (!$GLOBALS['isStopped']) {

                    $worker->join();
                    $isSuccess = $worker->isSuccess();
//
                    $state = ($isSuccess) ? ResponseState::Success : ResponseState::Failure;
                    //$response = ResponseHelper::createTaskResponse($state, $tasks[$i]);
                    //$this->_sender->send($response);
                }
            }
            //TODO Logout
        }
        else {

            $error = ErrorCodeHelper::BAD_INITIALIZATION;
            $this->_logger->error($error['message']);
            ResponseHelper::createErrorResponse($error);
        }
    }

    private function prepareTasks()
    {
        $preparedTasks = [];

        foreach ($this->_eyesAccounts as $eyesAccount) {

            $userAccounts = $this->getUserAccounts();
            $eyesAccount['userAccounts'] = $userAccounts;

            for ($i = 0; $i < count($eyesAccount['userAccounts']); $i++) {

                $eyesAccount['userAccounts'][$i]['message'] = $this->prepareMessage($eyesAccount, $eyesAccount['userAccounts'][$i], $this->getShuffledMessage());
            }

            array_push($preparedTasks, $eyesAccount);
        }

        return $preparedTasks;
    }

    /**
     * Prepare selected message with right account and user
     * @param $account
     * @param $user
     * @param $message
     * @return mixed
     */
    private function prepareMessage($account, $user, $message)
    {
        $fieldsToReplace = [
            '$User.Name$' => $user['Name'],
            '$Account.Name$' => $account['Name'],
            '$Account.Fullname$' => $account['Fullname'],
            '$User.PersonalMessage' => $user['PersonalMessage'],
            '$nl$' => "\n"
        ];

        $preparedMessage = $message['Content'];
        foreach ($fieldsToReplace as $field => $value) {

            $preparedMessage = str_replace($field, $value, $preparedMessage);
        }

        return $preparedMessage;
    }

    private function getShuffledMessage()
    {
        if (isset($this->_eyesMessages) && count($this->_eyesMessages) > 0) {

            $totalMessage = count($this->_eyesMessages);
            return ($this->_eyesMessages[rand(0, ($totalMessage - 1))]);
        }
    }

    private  function getUserAccounts()
    {
        $userAccounts = [];

        if (isset($this->_userAccounts) && count($this->_userAccounts) > 1) {

            for ($i = 0; $i < 1; $i++) {

                array_push($userAccounts, $this->_userAccounts[$i]);
                unset($this->_userAccounts[$i]);
            }
            $this->_userAccounts = array_values($this->_userAccounts);

            return $userAccounts;

        }
        else {
            $userAccounts = $this->_userAccounts;
            $this->_userAccounts = [];

            return $userAccounts;
        }

    }
}