<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:56
 */

namespace MessagingEngine;

use Configuration\Configuration;
use EventHandler\EventType;
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
        $this->_sender = $GLOBALS['sender'];
        $this->_logger = $GLOBALS['logger'];
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

//        var_dump(print_r($this->_userAccounts, 1));
//        var_dump(print_r($this->_eyesAccounts, 1));
//        var_dump(print_r($this->_eyesMessages, 1));
        if ($this->_userAccounts && $this->_eyesAccounts && $this->_eyesMessages) {

            $TotalMessages = 0;
            $tasks = $this->prepareTasks();


            foreach ($tasks as $task) {

                if (!$GLOBALS['isStopped']) {

                    $TotalMessages += count($task['userAccounts']);
                    $task = json_encode($task);
                    system('php instadm.php '."'".$task."' > /dev/null &");
                }
            }
            $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Update, ["totalMessages" => $TotalMessages]));
        }
        else {
            $this->_logger->error(ErrorCodeHelper::BAD_INITIALIZATION['message']);
            $this->_sender->send(ResponseHelper::createErrorResponse(ErrorCodeHelper::BAD_INITIALIZATION));
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

    private function getUserAccounts()
    {
        $userAccounts = [];

        if (isset($this->_userAccounts) && count($this->_userAccounts) > 41) {

            for ($i = 0; $i < 41; $i++) {

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