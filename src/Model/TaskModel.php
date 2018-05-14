<?php
/**
 * Created by IntelliJ IDEA.
 * User: Ilies
 * Date: 14/05/2018
 * Time: 18:44
 */

namespace Model;

class TaskModel
{
    private $_eyesAccountUsername;
    private $_eyesAccountPassword;
    private $_userAccounts;

    public function __construct($task)
    {
        if ($task && isset($task)) {

            $this->_eyesAccountUsername = $task['Username'];
            $this->_eyesAccountPassword = $task['Password'];
            $this->_userAccounts = $task['userAccounts'];
        }
    }

    /**
     * @return mixed
     */
    public function getEyesAccountUsername()
    {
        return $this->_eyesAccountUsername;
    }

    /**
     * @return mixed
     */
    public function getEyesAccountPassword()
    {
        return $this->_eyesAccountPassword;
    }

    /**
     * @return mixed
     */
    public function getUserAccounts()
    {
        return $this->_userAccounts;
    }
}