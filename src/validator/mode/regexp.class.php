<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Regexp
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Regexp extends Revship_Validator_Mode
{
    /**
     * @var string the regular expression to be matched with
     */
    public $pattern;
    /**
     * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
     * the regular expression defined via {@link pattern} should NOT match the attribute value.
     * @since 1.1.5
     **/
     public $not=false;
     
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
        if( ! $this->validateValue($value) )
        {
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is invalid.');
            $this->addError($this->formObject,$attribute,$message);
        }
    }
    
    
    public function validateValue($value)
    {
        if($this->pattern===null)
        {
            throw new Revship_Exception(500,'The "pattern" property must be specified with a valid regular expression.');
        }
        if((!$this->not && !preg_match($this->pattern,$value)) || ($this->not && preg_match($this->pattern,$value)))
        {
            return false;
        }
        return true;
    }
    

    public function genJsValidateAttribute($attribute)
    {
        /*
        $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is invalid.');
        
         * //@todo
         * 
        $ruleArray = array('email'=>'true');
        $messageArray = array('email'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
        */
    }
}