<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:55
 */

namespace RMQClient;

use Configuration\Configuration;
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

    private function initSender()
    {
        $this->_connection = new AMQPStreamConnection($this->_host, $this->_port, $this->_user, $this->_password);
        $this->_channel = $this->_connection->channel();
    }

    public function send()
    {
        try {

            $msg = new AMQPMessage('Hello World!');
            $this->_channel->basic_publish($msg, '', $this->_queue);

        } catch (\Exception $e) {

            $this->_logger->error(sprintf("Failed at sending message in queue : %s", $this->_queue));
            throw new $e;
        }
    }

    public function close()
    {
        $this->_channel->close();
        $this->_connection->close();
    }
}

