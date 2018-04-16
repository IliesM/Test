<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:55
 */

namespace RMQClient;

use Configuration\Configuration;
use Helpers\ErrorCodeHelper;
use Logger\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RMQSender
{
    /**
     * @var AMQPStreamConnection
     */
    private $_connection;
    private $_channelName;
    private $_rmqConfig;
    private $_host;
    private $_port;
    private $_user;
    private $_password;
    /**
     * @var AMQPChannel
     */
    private $_channel;
    private $_queue;
    /**
     * @var Logger
     */
    private $_logger;

    /**
     * RMQSender constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->_rmqConfig = $configuration->getConfig()['RMQConfig'];
        $this->_port = $this->_rmqConfig['port'];
        $this->_host = $this->_rmqConfig['host'];
        $this->_user = $this->_rmqConfig['user'];
        $this->_password = $this->_rmqConfig['password'];
        $this->_channelName = $this->_rmqConfig['channels']['phpToCSharp'];
        $this->_queue = $this->_rmqConfig['queues']['phpToCSharp'];
        $this->_logger = $GLOBALS['logger'];
        $this->initSender();
    }

    /**
     * Initialize RMQSender
     */
    private function initSender()
    {
        try {

            $this->_connection = new AMQPStreamConnection($this->_host, $this->_port, $this->_user, $this->_password);
            $this->_channel = $this->_connection->channel();

        } catch (\Exception $e) {

            $error = ErrorCodeHelper::CONNECTION_ERROR;
            $this->_logger->error(sprintf($error['message'], $this->_queue, $e->getMessage()));
            sprintf($error['message'], $this->_queue, $e->getMessage());
            exit(-1);
        }
    }

    /**
     * Send data to C# application
     */
    public function send($data)
    {
        try {

            $msg = new AMQPMessage($data);
            $this->_channel->basic_publish($msg, '', $this->_queue);

        } catch (\Exception $e) {

            $error = ErrorCodeHelper::ERROR_SENDING;
            $this->_logger->error(sprintf($error['message'], $this->_queue, $e->getMessage()));
            sprintf($error['message'], $this->_queue, $e->getMessage());
            $this->close();
        }
    }

    /**
     * Close channel and connection of the RMQSender
     */
    public function close()
    {
        $this->_channel->close();
        $this->_connection->close();
    }
}

