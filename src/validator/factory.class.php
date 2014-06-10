<?php
class Revship_Validator_Factory
{
    public function createValidator ($validatorName, &$formObject, $attributes, $otherOptions = null)
    {
        Revship::getLibClass('revship.validator.mode');
        
        $classNamespace =  'revship.validator.mode.' . strtolower($validatorName);
        $className = 'Revship_Validator_Mode_' . ucfirst($validatorName);
        Revship::getLibClass($classNamespace);
        $validator = new $className();
        $validator->init($formObject, $attributes, $otherOptions);
        return $validator;
    }
}
