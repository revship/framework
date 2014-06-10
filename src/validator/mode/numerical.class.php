<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Numerical
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Numerical extends Revship_Validator_Mode
{
    /**
     * @var boolean whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly=false;
    /**
     * @var integer|float upper limit of the number. Defaults to null, meaning no upper limit.
     */
    public $max;
    /**
     * @var integer|float lower limit of the number. Defaults to null, meaning no lower limit.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is too big.
     */
    public $tooBigMessage;
    /**
     * @var string user-defined error message used when the value is too small.
     */
    public $tooSmallMessage;
    
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
        if($this->integerOnly)
        {
            if(!preg_match('/^\s*[+-]?\d+\s*$/',"$value"))
            {
                $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} must be an integer.');
                $this->addError($this->formObject,$attribute,$message);
            }
        }
        else
        {
            if(!preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/',"$value"))
            {
                $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} must be a number.');
                $this->addError($this->formObject,$attribute,$message);
            }
        }
        if($this->min!==null && $value<$this->min)
        {
            $message=$this->tooSmallMessage!==null?Revship::l($this->tooSmallMessage):Revship::l('{attribute} is too small (minimum is {min}).');
            $this->addError($this->formObject,$attribute,$message,array('{min}'=>$this->min));
        }
        if($this->max!==null && $value>$this->max)
        {
            $message=$this->tooBigMessage!==null?Revship::l($this->tooBigMessage):Revship::l('{attribute} is too big (maximum is {max}).');
            $this->addError($this->formObject,$attribute,$message,array('{max}'=>$this->max));
        }
    }
    
    
    public function genJsValidateAttribute($attribute)
    {
        $ruleArray = array();
        $messageArray = array();
        
        if($this->integerOnly)
        {
            //integer
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} must be an integer.');
            $ruleArray['digits'] = 'true';
            $messageArray['digits']=$message;
        }
        else 
        {
            //number
            $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} must be a number.');
            $ruleArray['number'] = 'true';
            $messageArray['number']=$message;
        }
        if($this->min!==null)
        {
            //too small
            $message=$this->tooSmallMessage!==null?Revship::l($this->tooSmallMessage):Revship::l('{attribute} is too small (minimum is {min}).');
            $ruleArray['min'] = $this->min;
            $messageArray['min']=$message;
        }
        if($this->max!==null)
        {
            //too big
            $message=$this->tooBigMessage!==null?Revship::l($this->tooBigMessage):Revship::l('{attribute} is too big (maximum is {max}).');
            $ruleArray['max'] = $this->max;
            $messageArray['max']=$message;
        }
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray,array('{min}'=>$this->min,'{max}'=>$this->max));
    }
}