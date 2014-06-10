<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship_Validator_Mode_Email
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Validator_Mode_Email extends Revship_Validator_Mode
{
    
	/**
	 * @var string the regular expression used to validate the attribute value.
	 * @see http://www.regular-expressions.info/email.html
	 */
	public $pattern='/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
	/**
	 * @var string the regular expression used to validate email addresses with the name part.
	 * This property is used only when {@link allowName} is true.
	 * @since 1.0.5
	 * @see allowName
	 */
	public $fullPattern='/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';
	/**
	 * @var boolean whether to allow name in the email address (e.g. "Qiang Xue <qiang.xue@gmail.com>"). Defaults to false.
	 * @since 1.0.5
	 * @see fullPattern
	 */
	public $allowName=false;
    /**
	 * @var boolean whether to check the MX record for the email address.
	 * Defaults to false. To enable it, you need to make sure the PHP function 'checkdnsrr'
	 * exists in your PHP installation.
	 */
	public $checkMX=false;
	/**
	 * @var boolean whether to check port 25 for the email address.
	 * Defaults to false.
	 * @since 1.0.4
	 */
	public $checkPort=false;
	
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
			$message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is not a valid email address.');
			$this->addError($this->formObject,$attribute,$message);
		}
    }
    
    
    public function validateValue($value)
	{
		$valid=is_string($value) && (preg_match($this->pattern,$value) || $this->allowName && preg_match($this->fullPattern,$value));
		if($valid)
		{
			$domain=rtrim(substr($value,strpos($value,'@')+1),'>');
		}
		if($valid && $this->checkMX && function_exists('checkdnsrr'))
		{
			$valid=checkdnsrr($domain,'MX');
		}
		if($valid && $this->checkPort && function_exists('fsockopen'))
		{
			$valid=fsockopen($domain,25)!==false;
		}
		return $valid;
	}
	

    public function genJsValidateAttribute($attribute)
    {
        $message=$this->message!==null?Revship::l($this->message):Revship::l('{attribute} is not a valid email address.');
        $ruleArray = array('email'=>'true');
        $messageArray = array('email'=>$message);
        $this->addJsRule($this->formObject,$attribute,$ruleArray,$messageArray);
    }
}