<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Captcha
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
Revship::getLibClass('revship.validator.mode.remote');
class Revship_Validator_Mode_Captcha extends Revship_Validator_Mode_Remote
{
    public $url = '/ajax/validateCaptcha';
    
	public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is incorrect.');
        $ruleArray = array(
        'remote'=>"'{$this->url}'",
        'required' => 'true',
        );
        $messageArray = array('remote'=>$message, 'required'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}