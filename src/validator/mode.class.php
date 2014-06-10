<?php
/**
 * Validator Mode Parent Class
 * Should be extended from spec validator class
 * 
 * @author SLJ
 */
abstract class Revship_Validator_Mode
{
    public $attributesArray = array();
    public $formObject;
    public $message = null;
    /**
     * Call $child->validateAttribute() to validate each attribute
     * @param $object
     * @param $attributes
     */
    public function validate()
    {
        foreach($this->attributesArray as $attribute)
        {
            $attribute = trim($attribute);
            if(!empty($attribute))
            {
                $this->validateAttribute($attribute);
            }
        }
    }
    
    public function genJsValidate()
    {
        foreach($this->attributesArray as $attribute)
        {
            $attribute = trim($attribute);
            if(!empty($attribute))
            {
                $this->genJsValidateAttribute($attribute);
            }
        }
    }
    /**
     *
     */
    public function addJsRule($object,$attribute,$ruleArray,$messageArray,$params=array())
    {
        // Set up rules
		$object->addJsRule($attribute,$ruleArray);
		// Set up message
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		foreach($messageArray as $validateType => $message)
		{
		    $messageArray[$validateType] = strtr($message,$params);
		}
		$object->addJsMessage($attribute,$messageArray);
    }
    
    
    /**
     * Initialize the validator
     * Call from factory -> validator mode for validator
     * @param $formObject
     * @param $attributes
     * @param $otherOptions
     */
    public function init(&$formObject, $attributes, $otherOptions)
    {
        $this->formObject = $formObject;
        // convert attributes to array and save to mode class for spec validator
        $this->attributesArray = explode(',',$attributes);
        foreach ($this->attributesArray as $key => $attribute)
        {
            $this->attributesArray[$key] = trim($attribute);
        }
        if( is_array($otherOptions) && count($otherOptions) )
        {
            // assign other options to the spec validator's variables
             foreach( $otherOptions as $key => $value )
             {
                 $this->$key = $value;
             }   
        }
    }	
    
    /**
     * Add an error to form model.
     * Call from validator -> validator mode -> form model
     * @param object $object		From model object
     * @param string $attribute	Error attribute
     * @param string $message	Message text
     * @param array $params		Replace array
     */
    protected function addError($object,$attribute,$message='{attribute} is not valid.',$params=array())
	{
		$params['{attribute}']=$object->getAttributeLabel($attribute);
		$object->addError($attribute,strtr($message,$params));
	}
    
}
