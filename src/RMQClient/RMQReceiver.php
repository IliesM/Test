<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 12:49
 */

namespace RMQClient;


use Configuration\Configuration;
use EventHandler\EventHandler;
use Logger\Logger;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RMQReceiver
{
    /**
     * @var Logger
     */
    private $_logger;
    /**
     * @var AMQPChannel
     */
    private $_channel;
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
    private $_queue;

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

    public function receive()
    {
        try {

            $callback = function ($msg) {

                $this->_logger->info(sprintf("Message received %s", $msg->body));
                EventHandler::parseEvent($msg->body);
                $this->_logger->info("Message successfully proceed");
            };

            $this->_channel->basic_consume($this->_queue, '', false, true, false, false, $callback);

            while (count($this->_channel->callbacks)) {
                $this->_channel->wait();
            }

        } catch (\Exception $e) {

            $this->_logger->error(sprintf("Failed at retrieving message from queue %s : %s", $this->_queue, $e->getMessage()));
            throw new $e;
        }
    }

    public function close()
    {
        $this->_channel->close();
        $this->_connection->close();
    }
}
