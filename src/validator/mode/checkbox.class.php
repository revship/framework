<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Checkbox
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Checkbox extends Revship_Validator_Mode
{
    public $needChecked = false;
    
    public function validateAttribute($attribute)
    {
        $value=$this->formObject->$attribute;
        if( $this->needChecked && !$value )
        {
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} need to be checked.');
            $this->addError($this->formObject,$attribute,$message);
        }
    }
    

    public function genJsValidateAttribute($attribute)
    {
        if( $this->needChecked )
        {
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} need to be checked.');
            $ruleArray = array('required'=>'\':checked\'');
            $messageArray = array('required'=>$message);
        }
        
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}