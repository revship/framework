<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session
{
    private $_oObject = null;

    public function __construct()
    {
        if (!$this->_oObject)
        {
            $type = Revship::lib('config')->getItem('session.type');
            if(!$type)
                $sStorage = 'session.storage.session';
            else
                $sStorage = $type;
            /**
             * Using Cookie handler here because of problems with session_set_save_handler()
             * when using option 3 (sub-domains)
             * 
             * @link http://se2.php.net/manual/en/function.session-set-save-handler.php
             * @todo Find a work around for this problem
             */
            /*
            if (Phpfox::getParam('core.url_rewrite') == 3)
            {
                $sStorage = 'session.storage.cookie';
            }
            */
            $this->_oObject = Revship::lib($sStorage);
        }
        return $this->_oObject;
    }    
    
    
    public function &getInstance()
    {
        return $this->_oObject;
    }    
}

