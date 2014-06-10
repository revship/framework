<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Required
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Required extends Revship_Validator_Mode
{

    public function validateAttribute($attribute)
    {
        $value=$this->formObject->$attribute;
        if($this->isTrueEmpty($value))
        {
            $message=$this->message!==null?$this->message:Revship::l('{attribute} cannot be blank.');
            $this->addError($this->formObject,$attribute,$message);
        }
    }
    public function isTrueEmpty($value)
    {
        return $value===null || $value===array() || $value==='' || (is_scalar($value) && trim($value)==='');
    }
    
    
    public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?$this->message:Revship::l('{attribute} cannot be blank.');
        $ruleArray = array('required'=>'true');
        $messageArray = array('required'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}