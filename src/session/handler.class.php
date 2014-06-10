<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session_Handler
{
	private $_oObject = null;

	public function __construct()
	{
		if (!$this->_oObject)
		{
			$sStorage = 'session.handler.default';		
			/*
			if (defined('PHPFOX_IS_AJAX') && PHPFOX_IS_AJAX)
			{
				$sStorage = 'session.handler.file';			
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

?>