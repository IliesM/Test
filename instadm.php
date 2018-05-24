<?php

use Configuration\Configuration;
use EventHandler\ResponseState;
use Helpers\ErrorCodeHelper;
use Helpers\ResponseHelper;
use InstagramAPI\Instagram;
use Logger\Logger;
use Model\TaskModel;
use RMQClient\RMQSender;

require __DIR__.'/vendor/autoload.php';

class InstaDm {

    private $_task;
    private $_loginState;
    private $_sender;
    private $_logger;
    private $_ig;

    public function __construct(Configuration $config, TaskModel $task)
    {
        $this->_logger = new Logger($config);
        $this->_sender = new RMQSender($config, $this->_logger);
        $this->_task = $task;
        $this->_ig = $ig = new Instagram(false, false);
    }

    public function login()
    {

        if (!$this->_task && !isset($this->_task)) {
            $this->_logger->error(sprintf(ErrorCodeHelper::TASK_PARSING_ERROR['message'], getenv('CONTAINER')));
            exit;
        }
        $this->_loginState = $this->_ig->login($this->_task->getEyesAccountUsername(), $this->_task->getEyesAccountPassword());
        $this->_loginState = json_decode($this->_loginState, true)['status'];
       // $this->_loginState = "ok";

        if ($this->_loginState && isset($this->_loginState)) {

            if ($this->_loginState == "ok") {

                $this->process();
            }
            else if ($this->_task->getErrorCount() == 2) {

                $this->logout();
            }
            else
                $this->_task->addError();
        }
        else
            $this->logout();
    }

    public function process()
    {
       try {

           $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Logged, ['Username' => $this->_task->getEyesAccountUsername()]));

           foreach ($this->_task->getUserAccounts() as $userAccount) {

                $userAccount['eyesAccount'] = $this->_task->getEyesAccountUsername();
                $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Running, $userAccount));
                $this->_ig->direct->sendText(['users' => [$userAccount['UserID']]], $userAccount["message"]);
                $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Success, $userAccount));
                //sleep(100);
                sleep(rand(900, 1200));
           }

           $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Done, ['Username' => $this->_task->getEyesAccountUsername()]));
           $this->logout();

       } catch (\Exception $e) {

           //Renvoyer le compte en defaut à l'ui
           $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Failure, $userAccount));
           $this->_logger->error(sprintf(ErrorCodeHelper::INSTA_SEND_ERROR['message'], $e->getMessage()));
           $this->_sender->send(ResponseHelper::createErrorResponse(ErrorCodeHelper::INSTA_SEND_ERROR, ['Username' => $this->_task->getEyesAccountUsername()]));
       }
    }

    public function logout()
    {
        //$this->_logger->error(sprintf(ErrorCodeHelper::INSTA_LOGGIN_ERROR['message'], $this->_task->getEyesAccountUsername(), print_r($this->_loginState, 1)));
        //$this->_sender->send(ResponseHelper::createErrorResponse(ErrorCodeHelper::INSTA_LOGGIN_ERROR, $this->_task->getEyesAccountUsername()));
        $this->_ig->logout();
        $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::LoggedOut, ['Username' => $this->_task->getEyesAccountUsername()]));
        exit;
    }
}

$config = new Configuration(__DIR__.'/config/config.json');
$config->loadConfiguration();
$task = new TaskModel(json_decode($argv[1], true));

$instadm = new InstaDm($config, $task);
$instadm->login();

