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

set_time_limit(0);

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
        $this->_logger->info(print_r($task->getUserAccounts(), 1));
        $this->_ig = $ig = new Instagram(false, false);
    }

    public function login()
    {

        if (!$this->_task && !isset($this->_task)) {
            $this->_logger->error(sprintf(ErrorCodeHelper::TASK_PARSING_ERROR['message'], getenv('CONTAINER')));
            exit;
        }
       try {

           $this->_logger->info($this->_task->getEyesAccountUsername().' '.$this->_task->getEyesAccountPassword());
           $this->logout(true);
           $this->_loginState = $this->_ig->login($this->_task->getEyesAccountUsername(), $this->_task->getEyesAccountPassword());
           $this->_loginState = json_decode($this->_loginState, true)['status'];
           $this->_loginState = "ok";

           if ($this->_task->getErrorCount() != 2) {
               if ($this->_loginState && isset($this->_loginState)) {

                   if ($this->_loginState == "ok") {

                       $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Logged, ['Username' => $this->_task->getEyesAccountUsername()]));
                       sleep(20);
                       $this->process();
                   }
               }
           } else
               $this->logout();

       } catch (\Exception $e) {

           $this->_logger->info(sprintf("Error while login in : %s", $e->getMessage()));
           $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::LogginFailure, ['Username' => $this->_task->getEyesAccountUsername()]));
           //$this->logout();
       }
    }

    public function process()
    {
           foreach ($this->_task->getUserAccounts() as $userAccount) {

               try {

                    $userAccount['eyesAccount'] = $this->_task->getEyesAccountUsername();
                    $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Running, $userAccount));
                    $this->_ig->direct->sendText(['users' => [$userAccount['UserID']]], $userAccount["message"]);
                    $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Success, $userAccount));
                    sleep(rand(900, 1200));

                   }
                   catch (\Exception $e) {

                       //Renvoyer le compte en defaut à l'ui
                       $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Failure, $userAccount));
                   }
            }

        $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Done, ['Username' => $this->_task->getEyesAccountUsername()]));
        $this->logout();
    }

    public function logout($tryLogout = false)
    {
        try {
            if ($tryLogout == true) {
                $this->_ig->logout();
                return;
            }
            else {
                $this->_ig->logout();
                $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::LoggedOut, ['Username' => $this->_task->getEyesAccountUsername()]));
                exit;
            }

        } catch (\Exception $e) {
            $this->_logger->info(sprintf("Error while logout : %s", $e->getMessage()));
        }
    }
}

$config = new Configuration(__DIR__.'/config/config.json');
$config->loadConfiguration();
$task = new TaskModel(json_decode($argv[1], true));


$instadm = new InstaDm($config, $task);
$instadm->login();

