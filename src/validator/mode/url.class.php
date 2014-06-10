<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Url
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Url extends Revship_Validator_Mode
{
    /**
     * @var string the regular expression used to validates the attribute value.
     */
    public $pattern='/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';
    
    public function validateAttribute($attribute)
    {
        $value=$this->formObject->$attribute;
        /*
         * If value is empty then pass.
         * If not allow empty value, please add an extra rule as 'required'
         */
        if(empty($value))
        {
             return;   
        }
        if(!$this->validateValue($value))
        {
            $message=$this->message!==null?$this->message:Revship::l('{attribute} is not a valid URL.');
            $this->addError($this->formObject,$attribute,$message);
        }
    }

    public function validateValue($value)
    {
        return is_string($value) && preg_match($this->pattern,$value);
    }

    public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?$this->message:Revship::l('{attribute} is not a valid URL.');
        $ruleArray = array('url'=>'true');
        $messageArray = array('url'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}