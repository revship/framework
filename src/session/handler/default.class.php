<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Session_Handler_Default
{	
	public function init()
	{
		if(!isset($_SESSION))
		{
			session_start();	
		}
	}
}
