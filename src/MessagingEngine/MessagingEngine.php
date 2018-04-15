<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:56
 */

namespace MessagingEngine;

use Configuration\Configuration;
use RMQClient\RMQSender;
use Worker\Worker;

class MessagingEngine
{

    private $_userAccounts;
    private $_eyesAccounts;
    private $_eyesMessages;
    private $_taskQueue;
    private $_logger;
    private $_workers;
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

    }

    public function loadData()
    {
        $this->_userAccounts = ($GLOBALS['userAccounts']) ?: null;
        $this->_eyesAccounts = ($GLOBALS['eyesAccounts']) ?: null;
        $this->_eyesMessages = ($GLOBALS['eyesMessages']) ?: null;
    }

    public function startMessaging()
    {
        $this->loadData();

        if ($this->_userAccounts && $this->_eyesAccounts && $this->_eyesMessages) {

            $tasks = $this->prepareTasks();
            foreach ($tasks as $task) {

                $worker = new Worker($this->_sender);

                $worker->start();
            }
        }
        else {
            $this->_logger->error("Accounts/Messages/Users are not initialized");
            die;
        }
    }

    private function prepareTasks()
    {
        $preparedTasks = [];

        foreach ($this->_userAccounts as $userAccount) {

            $messages = $this->getMessagesBySex($userAccount['Sex']);
            $eyesAccount = $this->getEyesAccountBySex($userAccount['Sex']);

            $userAccount['messages'] = $messages;
            $userAccount['eyesAccount'] = $eyesAccount;
            $userAccount['messages'] = $this->prepareMessages($userAccount['eyesAccount'], $userAccount, $userAccount['messages']);
            array_push($preparedTasks, $userAccount);
        }

        return $preparedTasks;
    }

    private function prepareMessages($account, $user, $messages)
    {
        $preparedMessages = [];
        $fieldsToReplace = [
            '$User.Name$' => $user['Name'],
            '$Account.Name$' => $account['Name'],
            '$Account.Fullname$' => $account['Fullname'],
            '$nl$' => "\n"
        ];

        foreach ($messages as $message) {

            $preparedMessage = $message['Content'];
            foreach ($fieldsToReplace as $field => $value) {

                $preparedMessage = str_replace($field, $value, $preparedMessage);
            }

            array_push($preparedMessages, $preparedMessage);
        }

        return $preparedMessages;
    }

    private function getMessagesBySex($sex)
    {
        $foundMessages = [];

        foreach ($this->_eyesMessages as $message) {

            if ($message['To'] == $sex) {
                array_push($foundMessages, $message);
            }
        }

        return $foundMessages;
    }

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