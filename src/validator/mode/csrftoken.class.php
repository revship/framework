<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Csrftoken
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
class Revship_Validator_Mode_Csrftoken extends Revship_Validator_Mode
{

    public function validateAttribute($attribute)
    {
        $value=$this->formObject->$attribute;
        
        $valid=Revship::lib('csrftoken')->validateAndReset($value);
        
        if(!$valid)
        {
            $message=Revship::l('Token timeout. Please try again.');
            $this->addError($this->formObject,$attribute,$message);
        }
    }
    /*
    public function genJsValidateAttribute($attribute)
    {
    }
    */
}