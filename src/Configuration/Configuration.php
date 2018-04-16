<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:12
 */

namespace Configuration;

use Helpers\ErrorCodeHelper;

class Configuration
{
    /**
     * @var string
     */
    private $_configPath;
    /**
     * @var string
     */
    private $_loadedConfig;

    /**
     * Configuration constructor.
     * @param $_configPath
     */
    public function __construct($_configPath)
    {
        $this->_configPath = $_configPath;
    }

    /**
     * Load configuration from the Config/config.json file
     * @throws \Exception
     */
    public function loadConfiguration()
    {
            if ((isset($this->_configPath)) && (file_exists($this->_configPath))) {

                $this->_loadedConfig = file_get_contents($this->_configPath);
                $this->_loadedConfig = json_decode($this->_loadedConfig, true);

                if (json_last_error_msg() != 'No error') {

                    throw new \Exception(sprintf(ErrorCodeHelper::CONFIG_FILE_ERROR['message'], json_last_error_msg()));
                }

            } else
                throw new \Exception(sprintf(ErrorCodeHelper::CONFIG_FILE_NOT_FOUND['message']));
    }

    /**
     * Return the whole config
     * @return mixed
     */
    public function getConfig()
    {
        return $this->_loadedConfig;
    }


}