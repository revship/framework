<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session_Storage_Cookie
{
	private $_sPrefix = 'revship';
	
	public function __construct()
	{
		//$this->_sPrefix = Revship::getParam('core.session_prefix');
	}
		
	public function set($sName, $sValue)
	{
		Revship::setCookie($sName, $sValue);
	}
	
	public function get($sName)
	{
		$mCookie = Revship::getCookie($sName);
		
		return (empty($mCookie) ? false : $mCookie);
	}
	
	public function remove($mName)
	{
		if (!is_array($mName))
		{
			$mName = array($mName);
		}
		
		foreach ($mName as $sName)
		{
			Revship::setCookie($sName, '', -1);
		}
	}
	
	public function setArray($sName, $sValue)
	{
		$mCookie = Revship::getCookie($sName);
		if (!empty($mCookie))
		{
			$sValue = $mCookie . $sValue . ',';
		}
		
		$this->set($sName, $sValue);
	}
	
	public function getArray($sName, $sValue)
	{		
		$mCookie = Revship::getCookie($sName);			
		$aCookies = explode(',', $mCookie);
		
		if (in_array($sValue, $aCookies))
		{
			return true;
		}		
		
		return false;		
	}		
}

?>