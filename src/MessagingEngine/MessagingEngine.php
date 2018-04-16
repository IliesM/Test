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
use Helpers\ResponseHelper;
use Pool;
use RMQClient\RMQSender;
use Worker\MyWorker;

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
        $GLOBALS['sender'] = $this->_sender;

    }

    public function loadData()
    {
        $this->_userAccounts = ((isset($GLOBALS['userAccounts']))) ? $GLOBALS['userAccounts']: null;
        $this->_eyesAccounts = ((isset($GLOBALS['eyesAccounts']))) ? $GLOBALS['eyesAccounts']: null;
        $this->_eyesMessages = ((isset($GLOBALS['eyesMessages']))) ? $GLOBALS['eyesMessages']: null;
    }

    public function startMessaging()
    {
        $this->loadData();

        if ($this->_userAccounts && $this->_eyesAccounts && $this->_eyesMessages) {


            $tasks = $this->prepareTasks();
            $pool = new Pool(5, MyWorker::class, [$this->_sender, $this->_logger]);

            foreach ($tasks as $task) {

                $pool->submit(new MyWorker($this->_sender, $this->_logger));
                /*if (!$GLOBALS['isStopped']) {

                    $worker = new Worker($this->_sender, $this->_logger);

                    $worker->start();
                    $worker->join();

                    $response = ResponseHelper::createResponse(ResponseState::Success, "");
                    $this->_sender->send($response);
                } */

            }

            $pool->collect(function (MyWorker $workers) {
               foreach ($workers as $worker) {
                   
                   $this->_sender->send("ok");
               }
            });

            $pool->shutdown();
//            $pool->collect(function($checkingTask){
//                echo $checkingTask->val;
//                return $checkingTask->isGarbage();
//            });
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

            $message = $this->getMessageBySex($userAccount['Sex']);
            $eyesAccount = $this->getEyesAccountBySex($userAccount['Sex']);

            $userAccount['message'] = $message;
            $userAccount['eyesAccount'] = $eyesAccount;
            $userAccount['messages'] = $this->prepareMessage($userAccount['eyesAccount'], $userAccount, $userAccount['message']);
            array_push($preparedTasks, $userAccount);
        }

        return $preparedTasks;
    }

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