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
use Logger\Logger;
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
        $this->_sender = new RMQSender($configuration);
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
            foreach (range(1, (count($tasks) - 9)) as $i) {

                if (!$GLOBALS['isStopped']) {
                    $_workers[$i] = new Task($this->_logger);
                    $_workers[$i]->start();

                    $response = ResponseHelper::createTaskResponse(ResponseState::Running, $tasks[$i]);
                    $this->_sender->send($response);
                }
            }

            foreach (range(1,  (count($tasks) - 9)) as $i) {

                if (!$GLOBALS['isStopped']) {

                    $_workers[$i]->join();
                    $isSuccess = $_workers[$i]->isSuccess();

                    $state = ($isSuccess) ? ResponseState::Success : ResponseState::Failure;
                    $response = ResponseHelper::createTaskResponse($state, $tasks[$i]);
                    $this->_sender->send($response);
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

    /**
     * Prepare tasks with all needed data
     * @return array
     */
    private function prepareTasks()
    {
        $preparedTasks = [];

        foreach ($this->_userAccounts as $userAccount) {

            $message = $this->getMessageBySex($userAccount['Sex']);
            $eyesAccount = $this->getEyesAccountBySex($userAccount['Sex']);

            $userAccount['message'] = $message;
            $userAccount['eyesAccount'] = $eyesAccount;
            $userAccount['messages'] = $this->prepareMessage($userAccount['eyesAccount'], $userAccount, $userAccount['message']);
            array_push($preparedTasks, $userAccount);
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
            '$nl$' => "\n"
        ];

        $preparedMessage = $message['Content'];
        foreach ($fieldsToReplace as $field => $value) {

            $preparedMessage = str_replace($field, $value, $preparedMessage);
        }

        return $preparedMessage;
    }

    /**
     * Get message by the sex of the receiver
     * @param $sex
     * @return mixed
     */
    private function getMessageBySex($sex)
    {
        $foundMessages = [];

        foreach ($this->_eyesMessages as $message) {

            if ($message['To'] == $sex) {
                array_push($foundMessages, $message);
            }
        }

        return $foundMessages[mt_rand(0, 4)];
    }

    /**
     * Get eyes account by user sex
     * @param $sex
     * @return null
     */
    private function getEyesAccountBySex($sex)
    {
        $foundAccount = null;

        foreach ($this->_eyesAccounts as $account) {

            if ($sex == 'M')
                $sex = 'F';
            else
                $sex = 'F';

            if ($account['Sexe'] == $sex)
                $foundAccount = $account;
        }

        return $foundAccount;
    }
}