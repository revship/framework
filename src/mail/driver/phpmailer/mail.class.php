<?php

defined('REVSHIP') or exit('Access Denied!');

class Revship_Mail_Driver_Phpmailer_Mail implements Revship_Mail_Interface
{    
    private $_oMail = null;
    
    public function __construct()
    {
        if (!file_exists( LIB_PATH . 'phpmailer' . DS . 'class.phpmailer.php'))
        {
            new Revship_Exception(404, 'Unable to load lib: ' . LIB_PATH . 'phpmailer' . DS . 'class.phpmailer.php', E_USER_ERROR);
        }
        
        require_once(LIB_PATH . 'phpmailer' . DS . 'class.phpmailer.php');
        
        $this->_oMail = new PHPMailer;
        $this->_oMail->From = Revship::lib('config')->getItem('mail.fromEmail') ? Revship::lib('config')->getItem('mail.fromEmail') : 'revship@localhost';
        $this->_oMail->FromName = Revship::lib('config')->getItem('mail.fromName') ? Revship::lib('config')->getItem('mail.fromName') : Revship::lib('config')->getItem('site.title');        
        $this->_oMail->WordWrap = 75;       
        $this->_oMail->CharSet = 'utf-8'; 
    }
    
    public function send($mTo, $sSubject, $sTextPlain, $sTextHtml, $sFromName = null, $sFromEmail = null)
    {        
        $this->_oMail->AddAddress($mTo);
        $this->_oMail->Subject = $sSubject;
        $this->_oMail->Body = $sTextHtml;
        $this->_oMail->AltBody = $sTextPlain;
        
        if ($sFromName !== null)
        {
            $this->_oMail->FromName = $sFromName;
        }
        
        if ($sFromEmail !== null)
        {        
            $this->_oMail->From = $sFromEmail;
        }
        
        if(!$this->_oMail->Send())
        {
            $this->_oMail->ClearAddresses();
            new Revship_Exception(501,'Mail error on sending: '.$this->_oMail->ErrorInfo, E_USER_ERROR);
        }
        $this->_oMail->ClearAddresses();
        
        return true;
    }
}

?>