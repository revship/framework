<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Lettersonly
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
Revship::getLibClass('revship.validator.mode.regexp');
class Revship_Validator_Mode_Lettersonly extends Revship_Validator_Mode_Regexp
{
    /**
     * @var string the regular expression to be matched with
     */
    public $pattern = '/^[a-zA-Z]+$/';
    public $message = "{attribute} only letters are valid, numbers and symbols are not allowed";
    

    public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is invalid.');
        $ruleArray = array('lettersonly'=>'true');
        $messageArray = array('lettersonly'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
        
    }
}