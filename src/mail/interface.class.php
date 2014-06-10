<?php
defined('REVSHIP') or exit('Access Denied!');

interface Revship_Mail_Interface
{
	public function send($to, $subject, $textPlain, $textHtml, $fromName = null, $fromEmail = null);
}

?>