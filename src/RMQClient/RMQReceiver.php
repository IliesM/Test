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
use EventHandler\ResponseState;
use Helpers\ErrorCodeHelper;
use Helpers\ResponseHelper;
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
    private $_sender;

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
        $this->_channelName = "ui".getenv("CONTAINER");/*$this->_rmqConfig['channels']['cSharpToPhp'];*/
        $this->_queue = "ui".getenv("CONTAINER");/*$this->_rmqConfig['queues']['cSharpToPhp'];*/
        $this->_logger = $GLOBALS['logger'];
        $this->_sender = $GLOBALS['sender'];

        $this->initReceiver();
    }

    /**
     * Initialize RMQReceiver
     */
    private function initReceiver()
    {
       try {

           $this->_connection = new AMQPStreamConnection($this->_host, $this->_port, $this->_user, $this->_password);
           $this->_channel = $this->_connection->channel();
           $this->_channel->queue_declare($this->_queue, false, false, false, false);

       } catch (\Exception $e) {

           $this->_logger->error(sprintf(ErrorCodeHelper::CONNECTION_ERROR['message'], $this->_queue.' '.$e->getMessage(), $e->getMessage()));
           exit(-1);
       }
    }

    /**
     * Receive and parse data from C# application
     */
    public function receive()
    {
        $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::Ready, null));
        try {
            $callback = function ($msg) {

                $this->_logger->info(sprintf("Message received %s", $msg->body));

                if (EventHandler::parseEvent($msg->body) == -1)
                    $this->close();
                $this->_logger->info("Message successfully proceed");
            };

            $this->_channel->basic_consume($this->_queue, '', false, true, false, false, $callback);

            while (count($this->_channel->callbacks)) {
                $this->_channel->wait();
            }

        } catch (\Exception $e) {

            $error = ErrorCodeHelper::ERROR_RETRIEVING;
            $this->_logger->error(sprintf($error['message'], $this->_queue, $e->getMessage()));
            $this->close();
        }
    }

    /**
     * Close channel and connection of the RMQReceiver
     */
    public function close()
    {
        $this->_sender->send(ResponseHelper::createTaskResponse(ResponseState::NotReady, null));
        $this->_logger->info(sprintf(ErrorCodeHelper::CLOSE_CONNECTION['message']));
        $this->_channel->close();
        $this->_connection->close();
        exit;
    }
}
