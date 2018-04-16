<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:12
 */

namespace Configuration;

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
     */
    public function loadConfiguration()
    {
        if ((isset($this->_configPath)) && (file_exists($this->_configPath))) {
            $this->_loadedConfig = file_get_contents($this->_configPath);
            $this->_loadedConfig = json_decode($this->_loadedConfig, true);
        }
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