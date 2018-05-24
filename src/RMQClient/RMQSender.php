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
     * @param Logger $logger
     */
    public function __construct(Configuration $configuration, Logger $logger)
    {
        $this->_rmqConfig = $configuration->getConfig()['RMQConfig'];
        $this->_port = $this->_rmqConfig['port'];
        $this->_host = $this->_rmqConfig['host'];
        $this->_user = $this->_rmqConfig['user'];
        $this->_password = $this->_rmqConfig['password'];
        $this->_channelName = "container".getenv("CONTAINER");/*$this->_rmqConfig['channels']['phpToCSharp'];*/
        $this->_queue = "container".getenv("CONTAINER");/*$this->_rmqConfig['queues']['phpToCSharp'];*/
        $this->_logger = $logger;
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
            $this->_channel->queue_declare($this->_queue, false, false, false, false);

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
            if (isset($data)) {
                $msg = new AMQPMessage($data);
                $this->_channel->basic_publish($msg, '', $this->_queue);
            }

        } catch (\Exception $e) {

            $this->_logger->error(sprintf(ErrorCodeHelper::ERROR_SENDING['message'], $this->_queue, $e->getMessage()));
            $this->close();
        }
    }

    public function purge()
    {
        $this->_channel->queue_purge($this->_queue);
    }

    /**
     * Close channel and connection of the RMQSender
     */
    public function close()
    {
        $this->_logger->info(sprintf(ErrorCodeHelper::CLOSE_CONNECTION['message']));
        $this->_channel->close();
        $this->_connection->close();
    }
}

