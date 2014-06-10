<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Remote
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Remote extends Revship_Validator_Mode
{
    public $url;
    public function validateAttribute($attribute)
    {
        /*
        $value=$this->formObject->$attribute;
        if( $this->needChecked && !$value )
        {
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} need to be checked.');
            $this->addError($this->formObject,$attribute,$message);
        }*/
    }
    

    public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is not available.');
        $ruleArray = array('remote'=>"'{$this->url}'");
        $messageArray = array('remote'=>$message);
        
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}