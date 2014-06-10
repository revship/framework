<?php

defined('REVSHIP') or exit('Access Denied!');

Revship::getLibClass('revship.mail.interface');

class Revship_Mail
{
    private $_mailer = null;
    private $to = null;
    private $subject = null;
    private $fromName = null;
    private $fromEmail = null;
    private $messagePlain = null;
    private $messageHeader = true;
    
    // we will use local variables in the send function for the html vs plain text
    private $message = null;
    
    public function __construct($method = null)
    {        
            $this->_mailer = Revship::lib('mail.driver.phpmailer.' . ($method === null ? Revship::lib('config')->getItem('mail.method') : $method));        
    }    
    
    public function test($aVals)
    {
            $this->_mailer->test($aVals);
            return $this;
    }
    
    public function to($mTo)
    {        
            $this->to = $mTo;
            return $this;
    }
    
    public function subject($sSubject,$aReplace=null)
    {
        if($aReplace)
        {
            $sSubject = strtr($sSubject,$aReplace);
        }
        $this->subject = html_entity_decode($sSubject, null, 'UTF-8');
        return $this;
    }
    
    public function fromName($sFromName)
    {
        $this->fromName = $sFromName;
        return $this;
    }
    
    
    public function fromEmail($sFromEmail)
    {
        $this->fromEmail = $sFromEmail;
        return $this;
    }
    /**
     * Assign subject and content with a template setting. Auto replace the temp vars
     * @param string $emailTemplate     e.g.  resetpassword   (so subject will be mail.template.resetpassword.subject, content will be mail.template.resetpassword.body)
     * @param array $extraReplaceArray    replacementArray, this can attach new replacement phrases or modify an existing phrase.
     */
    public function templateViaConfig($emailTemplate, $extraReplaceArray = array())
    {
        $subject = Revship::l(Revship::lib('config')->getItem('mail.template.'.$emailTemplate.'.subject'));
        $message = Revship::l(Revship::lib('config')->getItem('mail.template.'.$emailTemplate.'.body'));
        $replaceArray = $this->genGlobalReplaceVarsArray($extraReplaceArray);
        $this ->subject($subject, $replaceArray);
        $this ->message($message, $replaceArray);
        return $this;
    }
    protected function genGlobalReplaceVarsArray($extraReplaceArray = array())
    {
        $replace =  array(
                            '{{site.title}}'=>Revship::lib('config')->getItem('site.title'),
                            '{{site.domainUrl}}'=>Revship::lib('config')->getItem('site.domainUrl'),
                            );
        return array_merge((array)$replace, (array)$extraReplaceArray);
    }
    public function message($sMessage,$aReplace=null)
    {
        if ($aReplace) 
        {
            $sMessage = strtr($sMessage, $aReplace);
        }
        $this->message = $sMessage;
        return $this;
    }
    
    public function messageHeader($bMessageHeader)
    {
        $this->messageHeader = $bMessageHeader;
        return $this;
    }

    public function messagePlain($sMessage)
    {
        $this->messagePlain = $sMessage;
        return $this;
    }

    /**
     * Checks: 
     *  -    (message || to) === null -> return false;
     *     -    (sFromName || sFromEmail) === null -> getParam(core.
     *
     * @example Revship::lib('mail')->to('user@email.com')->subject('Test Subject')->message('This is a test message')->send();
     * @example Revship::lib('mail')->to(array('user1@email.com', 'user2@email.com', 'user3@email.com')->subject('Test Subject')->message('This is a test message')->send()
     *
     * @return boolean
     */
    public function send($bDoCheck = false)
    {
        // turn into an array
        if (! is_array($this->to)) {
            $this->to = array (
                    $this->to 
            );
        }
        if ($this->message === null || $this->to === null) {
            return false;
        }
        if ($this->fromName === null) {
            $this->fromName = Revship::lib('config')->getItem('mail.fromName');
        }
        if ($this->fromEmail === null) {
            $this->fromEmail = Revship::lib('config')->getItem('mail.fromEmail');
        }
        $this->fromName = html_entity_decode($this->fromName, null, 'UTF-8');
        
        $bIsSent = true;
        
        if (! empty($this->to)) {
            foreach ( $this->to as $sEmail ) {
                $sEmail = trim($sEmail);
                // Load plain text template
                $sTextPlain = $this->messagePlain !== null ? $this->messagePlain : strip_tags($this->message);
                // Load HTML text template
                $_tpl = Revship::lib('template');
                $_tpl->assign('data', array (
                        'message' => $this->message  // str_replace("\n", "<br />", $this->message),
                ));
                $sTextHtml = $_tpl->fetch('email/template.html');
                
                $bIsSent = $this->_mailer->send($sEmail, $this->subject, $sTextPlain, $sTextHtml, $this->fromName, $this->fromEmail);
            }
        }
        
        return $bIsSent;
    }
    
}
