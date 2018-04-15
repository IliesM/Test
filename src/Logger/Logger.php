<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 10:56
 */

namespace Logger;

use Configuration\Configuration;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;

class Logger implements ILogger
{

    /**
     * @var \Monolog\Logger
     */
    private $_logger;
    /**
     * @var array
     */
    private $_loggerConfig;
    /**
     * @var string
     */
    private $_fileFormat;
    /**
     * @var string
     */
    private $_logDir;
    /**
     * @var string
     */
    private $_outputFormat;
    /**
     * @var string
     */
    private $_dateFormat;

    /**
     * Logger constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->_loggerConfig = $configuration->getConfig()['LoggerConfig'];
        $this->_fileFormat = $this->_loggerConfig['logFileFormat'];
        $this->_logDir = $this->_loggerConfig['logDir'];
        $this->_outputFormat = $this->_loggerConfig['outputFormat'];
        $this->_dateFormat = $this->_loggerConfig['dateFormat'];
        $this->initLogger();
    }

    private function initLogger()
    {
        $this->_logger = new \Monolog\Logger('InstaDMLogger');

        $this->_logger->pushHandler($this->getStream());
    }

    private function getFormatter()
    {
        return (new LineFormatter($this->_outputFormat, $this->_dateFormat));
    }

    private function getStream()
    {
        $stream = new RotatingFileHandler($this->_logDir.'/'.$this->_fileFormat);
        $stream->setFormatter($this->getFormatter());

        return ($stream);
    }

    public function info($message, $extraInfo = [])
    {
        $this->_logger->info($message, $extraInfo);
    }

    public function warn($message, $extraInfo = [])
    {
        $this->_logger->warn($message, $extraInfo);
    }

    public function error($message, $extraInfo = [])
    {
        $this->_logger->error($message, $extraInfo);
    }

    public function debug($message, $extraInfo = [])
    {
        $this->_logger->debug($message, $extraInfo);
    }
}