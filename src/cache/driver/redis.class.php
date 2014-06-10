<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Cache_Driver_Redis
{
    protected $conn = null;
    protected $config = null;
    /**
     * @param array $config array('driver'=>'redis', 'host' => '127.0.0.1', 'port' => 6379,'db' => 16, 'weight' => 1, 'password' => '1234','timeout' => 3)
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }
    public function connect()
    {
        if($this->config['driver'] != 'redis'){
            throw new Revship_Exception(500,__METHOD__.'. Invalid Redis Config');
            return false;
        }
        // Already there?
        try{
            if( $this->conn != null
                && $this->conn instanceof Redis
                && '+PONG' == $this->conn->ping() )
            {
                return $this->conn;
            }
        }
        catch(Exception $e){}
        try{
            $this->conn = new Redis();
            $this->conn->connect($this->config['host'],$this->config['port'],$this->config['timeout']);
            if( isset($this->config['password']) && !empty($this->config['password']))
            {
                $this->conn->auth($this->config['password']);
            }
            if( isset($this->config['db']) && !empty($this->config['db']))
            {
                $this->conn->select($this->config['db']);
            }
            return $this->conn;
        }
        catch(Exception $e){
            Revship::log(__METHOD__.'-'.$e->getMessage(), 'REDIS');
        }
    }
    
    public function __destruct()
    {
        $this->close();
    }
    public function close()
    {
        if($this->conn instanceOf Redis)
        {
            $this->conn->close();
            $this->conn = null;
        }
    }
    public function __call($name, $arguments)
    {
        $this->connect();
        return call_user_func_array($this->conn->$name, $arguments);
    }
}