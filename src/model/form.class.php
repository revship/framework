<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship Model Form
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
 
class Revship_Model_Form extends Revship_Model
{
    /*
     * Error array
     */
    public $errorArray = array();
    
    /*
     * Validate Config Array
     */
    public $validators = null;
    
    public $jsValidateRules = array();
    public $jsValidateMessages = array();
    /**
     * Validate from validators
     * 
     * @return bool    Pass the validators?
     */
    public function validate($attributes=null)
    {
        $this->clearErrors();
        foreach($this->getValidators() as $validator)
        {
            //$this is spec form object
            $validator->validate();
        }
        return !$this->hasErrors();
    }
    /**
     * 
     * @param unknown_type $attribute
     */
    public function hasErrors($attribute=null)
    {
        if($attribute===null)
            return count($this->errorArray)!=0;
        else
            return isset($this->errorArray[$attribute]);
    }
    /**
     * Add an error message.
     * Called from validator -> validator mode -> form model
     * @param $attribute
     * @param $message
     */
    public function addError($attribute,$message)
    {
        $this->errorArray[$attribute] = $message;
    }
    /**
     * Get attribute label
     * Called from validator.mode->addError()
     * @param unknown_type $attribute
     */
    public function getAttributeLabel($attribute)
    {
        $attribute = trim($attribute);
        $labelsArray = $this->attributeLabels();
        if(isset($labelsArray[$attribute]))
        {
            return Revship::l($labelsArray[$attribute]);
        }
        return Revship::l($attribute);
    }
    /**
     * Returns a value indicating whether the attribute is required. 
     * This is determined by checking if the attribute is associated with a Revship_Validator_Mode_Required validation rule in the current scenario
     */
    public function isAttributeRequired($attribute)
    {
        foreach($this->getValidators($attribute) as $validator)
        {
            if($validator instanceof Revship_Validator_Mode_Required)
                return true;
        }
        return false;
    }
    public function getAttributeMaxLength($attribute)
    {
        foreach($this->getValidators($attribute) as $validator)
        {
            if($validator instanceof Revship_Validator_Mode_Length)
                return $validator->max;
        }
        return false;
    }
    /**
     * Empty all errors
     */
    protected function clearErrors()
    {
        $this->errorArray = array();
    }
    /**
     * Create Validators
     */
    public function createValidators()
    {
        $validators = array();
        foreach($this->rules() as $rule)
        {
            if(isset($rule[0],$rule[1]))  // attributes, validator name
            {
                $validators[]=Revship::lib('validator')->createValidator($rule[1],$this,$rule[0],array_slice($rule,2)); // validator name, $this, attributes,  other options
            }
            else
            {
                new Revship_Exception(501,'['.get_class($this).'] has an invalid validation rule. The rule must specify attributes to be validated and the validator name.');
            }
        }
        return $validators;
    }
    public function getValidators($attribute=null)
    {
        if($this->validators===null)
        {
            $this->validators=$this->createValidators();
        }
        $validators=array();
        
        foreach($this->validators as $validator)
        {
        //    if($validator->applyTo())
        //    {
                if($attribute===null || in_array($attribute,$validator->attributesArray,true))
                {
                    $validators[]=$validator;
                }
        }
        return $validators;
    }
    
    /**
     * Assign array to variables
     * 
     * $a = array('email' => 'a@b.com',
     *               'password' => 'abc'
     *             );
     * will assign to 
     * $this->email = 'a@b.com';
     * $this->password = 'abc';
     * 
     * @param $array
     */
    public function attributes($array)
    {
        if( ! is_array($array) || ! count($array) )
        {
            return false;   
        }
        foreach($array as $key => $value)
        {
            $this->$key=$value;
        }
        return true;
    }
    /**
     * Return error array
     */
    public function getLastError()
    {
        return $this->errorArray;
    }
    /**
     * Generate Js Validator
     */
    public function genJsValidator($formId)
    {
        //gen array
        foreach($this->getValidators() as $validator)
        {
            //$this is spec form object
            $validator->genJsValidate();
        }
        //gen rules html
        $ruleHtml = '';
        foreach($this->jsValidateRules as $key => $rules)
        {
            $ruleHtml.="'{$formId}[{$key}]':{\n";
            foreach($rules as $validateType => $value)
            {
                    $ruleHtml.="{$validateType}:{$value},\n";
            }
            $ruleHtml.="},\n";
        }
       
        //gen msg html
        $msgHtml = '';
        foreach($this->jsValidateMessages as $key => $rules)
        {
            $msgHtml.="'{$formId}[{$key}]':{\n";
            foreach($rules as $validateType => $value)
            {
                $msgHtml.="{$validateType}:'{$value}',\n";
            }
            $msgHtml.="},\n";
        }
        
        return Revship::lib('html')->script("
$(function(){
    $('#{$formId}').validate({
        rules: {
{$ruleHtml}
        },
        messages: {
{$msgHtml}
        }
    });
});");
    }
    public function addJsRule($attribute,$ruleArray)
    {
        if(isset($this->jsValidateRules[$attribute]))
        {
            $this->jsValidateRules[$attribute]=array_merge($this->jsValidateRules[$attribute],$ruleArray);
        }
        else
        {
            $this->jsValidateRules[$attribute]=$ruleArray;
        }
    }
    public function addJsMessage($attribute,$messageArray)
    {
        if(isset($this->jsValidateMessages[$attribute]))
        {
            $this->jsValidateMessages[$attribute]=array_merge($this->jsValidateMessages[$attribute],$messageArray);
        }
        else
        {
            $this->jsValidateMessages[$attribute]=$messageArray;
        }
    }
    
    public function __get($property_name)
    {
        if(isset($this->$property_name))
        {
            return($this->$property_name);
        }
        else
        {
            return(NULL);
        }
     }
    
    public function __set($property_name,$value)
    {
        $this->$property_name=$value;
    }
    public function rules()
    {
        return array();
    }
    public function attributeLabels()
    {
        return array();
    }
}
