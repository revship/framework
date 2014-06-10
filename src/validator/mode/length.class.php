<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Length
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Length extends Revship_Validator_Mode
{
    /**
     * @var integer maximum length. Defaults to null, meaning no maximum limit.
     */
    public $max;
    /**
     * @var integer minimum length. Defaults to null, meaning no minimum limit.
     */
    public $min;
    /**
     * @var integer exact length. Defaults to null, meaning no exact length limit.
     */
    public $is;
    /**
     * @var string user-defined error message used when the value is too short.
     */
    public $tooShortMessage;
    /**
     * @var string user-defined error message used when the value is too long.
     */
    public $tooLongMessage;
    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
     * Setting this property requires you to enable mbstring PHP extension.
     * The value of this property will be used as the 2nd parameter of the mb_strlen() function.
     * Defaults to false, which means the strlen() function will be used for calculating the length
     * of the string.
     * @since 1.1.1
     */
    public $encoding=false;
    
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
        
        if($this->encoding!==false && function_exists('mb_strlen'))
        {
            $length=mb_strlen($value,$this->encoding);
        }
        else 
        {
            $length=strlen($value);
        }
        
        if($this->min!==null && $length<$this->min)
        {
            $message=$this->tooShortMessage!==null?Revship::l($this->tooShortMessage):Revship::l('{attribute} is too short (minimum is {min} characters).');
            $this->addError($this->formObject,$attribute,$message,array('{min}'=>$this->min));
        }
        
        if($this->max!==null && $length>$this->max)
        {
            $message=$this->tooLongMessage!==null?Revship::l($this->tooLongMessage):Revship::l('{attribute} is too long (maximum is {max} characters).');
            $this->addError($this->formObject,$attribute,$message,array('{max}'=>$this->max));
        }
        
        if($this->is!==null && $length!==$this->is)
        {
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is of the wrong length (should be {length} characters).');
            $this->addError($this->formObject,$attribute,$message,array('{length}'=>$this->is));
        }
    }
    
    public function genJsValidateAttribute($attribute)
    {
        $ruleArray = array();
        $messageArray = array();
        
        if($this->min!==null)
        {
            // min length
            $message=$this->tooShortMessage!==null?Revship::l($this->tooShortMessage):Revship::l('{attribute} is too short (minimum is {min} characters).');
            $ruleArray['minlength'] = $this->min;
            $messageArray['minlength']=$message;
        }
        if($this->max!==null)
        {
            // max length
            $message=$this->tooLongMessage!==null?Revship::l($this->tooLongMessage):Revship::l('{attribute} is too long (maximum is {max} characters).');
            $ruleArray['maxlength'] = $this->max;
            $messageArray['maxlength']=$message;
        }
        if($this->is!==null)
        {
            // is length
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is of the wrong length (should be {length} characters).');
            $ruleArray['rangelength'] = '['.$this->is.','.$this->is.']';
            $messageArray['rangelength']=$message;
        }
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray,array('{min}'=>$this->min, '{max}'=>$this->max, '{length}'=>$this->is));
    }
 
}