<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session_Storage_Session
{
    private $_sPrefix = 'revship';
    
    private $_aCookie = array();
    
    public function __construct()
    {
        //$this->_sPrefix = Revship::getParam('core.session_prefix');
    }
        
    public function set($sName, $sValue)
    {
        $_SESSION[$this->_sPrefix][$sName] = $sValue;
        return true;
    }
    
    public function get($sName)
    {    
        if (isset($_SESSION[$this->_sPrefix][$sName]))
        {
            return $_SESSION[$this->_sPrefix][$sName];
        }

        return false;        
    }
    
    public function remove($mName)
    {
        if (!is_array($mName))
        {
            $mName = array($mName);
        }
        //(($sPlugin = Phpfox_Plugin::get('session_remove__start')) ? eval($sPlugin) : false);
        foreach ($mName as $sName)
        {
            unset($_SESSION[$this->_sPrefix][$sName]);
        }
    }
    
    public function setArray($sName, $sValue)
    {
        $_SESSION[$this->_sPrefix][$sName]['value_' . $sValue] = true;
    }
    
    public function getArray($sName, $sValue)
    {        
        if (isset($_SESSION[$this->_sPrefix][$sName]['value_' . $sValue]))
        {
            return true;
        }
        
        return false;
    }    
    
//@todo copy to storage.cookie
    public function setFlash($zone,$value)
    {
        $this->set('flash_'.$zone,$value);
    }
    public function getFlash($zone)
    {
        $msg = $this->get('flash_'.$zone);
        $this->remove('flash_'.$zone);
        return $msg;
    }
    public function hasFlash($zone)
    {
        if($this->get('flash_'.$zone)!==false)
        {
            return true;
        }
        return false;
    }
    
}
