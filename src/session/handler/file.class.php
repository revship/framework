<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session_Handler_File
{
	private $_sSavePath = '';
	
	private $_sPrefix = 'sess_';
	
	public function init()
	{		
		session_set_save_handler(
				array($this, 'open'),
				array($this, 'close'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destroy'),
				array($this, 'gc')			
		);		
		
			$sSessionSavePath = session_save_path();
			
		if (empty($sSessionSavePath) || (!empty($sSessionSavePath) && !Revship::lib('file')->isWritable($sSessionSavePath)))
		{
			$this->_sSavePath = rtrim(Revship::lib('file')->getTempDir(), DS) . DS;
		}
		else 
		{
			$this->_sSavePath = rtrim($sSessionSavePath, DS) . DS;
		}
		
		if (!Revship::lib('file')->isWritable($this->_sSavePath))
		{
			new Revship_Exception(501,'Session path is not wriable: ' . $this->_sSavePath, E_USER_ERROR);
		}
		
		if(!isset($_SESSION))
		{
			session_start();	
		}
	}
	
	public function open()
	{	  
		return true;
	}
	
	public function close()
	{
		return true;
	}
	
	public function read($iId)
	{
		if (!file_exists($this->_sSavePath . $this->_sPrefix . $iId))
		{
			return false;
		}
		
		return (string) file_get_contents($this->_sSavePath . $this->_sPrefix . $iId);
	}
	
	public function write($iId, $mData)
	{  	  
		if ($hFp = @fopen($this->_sSavePath . $this->_sPrefix . $iId, "w")) 
		{
	    	$bReturn = fwrite($hFp, $mData);
	    	fclose($hFp);
	    	
	    	return $bReturn;
	  	} 
	  	else 
	  	{
	    	return(false);
	  	}	
	}
	
	public function destroy($iId)
	{
		return(@unlink($this->_sSavePath . $this->_sPrefix . $iId));
	}
	
	public function gc($iMaxLifetime)
	{
		foreach (glob($this->_sSavePath . $this->_sPrefix . '*') as $sFilename) 
		{
	    	if (filemtime($sFilename) + $iMaxLifetime < time()) 
	    	{
	      		@unlink($sFilename);
	    	}
	  	}
	  	return true;
	}	
}

?>