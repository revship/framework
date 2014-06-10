<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Cache
{
    private $_oObject = null;

    /**
     *
     * @param array $config array('driver'=>'redis', 'host' => '127.0.0.1', 'port' => 6379,'db' => 16, 'weight' => 1, 'password' => '1234','timeout' => 3)
     */
    public function __construct($config)
    {
        if (!$this->_oObject)
        {
            if(isset($config['driver']))
            {
                $this->_oObject = Revship::lib('cache.driver.'.$config['driver'], $config);
            }
        }
        return $this->_oObject;
    }
    public function &getInstance()
    {
        return $this->_oObject;
    }    
    public function encodeKey($key) {
        return urlencode($key);
    }
}