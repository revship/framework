<?php

class Revship_Csrftoken
{
    /**
     * Prefix to the session variable name used by the action.
     */
    const SESSION_KEY='Revship.Csrftoken';
    /**
     * @var string the fixed verification code. When this is property is set,
     * {@link getVerifyCode} will always return this value.
     * This is mainly used in automated tests where we want to be able to reproduce
     * the same verification code each time we run the tests.
     * Defaults to null, meaning the verification code will be randomly generated.
     */
    public $fixedVerifyCode;

    
    /**
     * Validates the input to see if it matches the generated code.
     * @param string $input user input
     * @param boolean $caseSensitive whether the comparison should be case-sensitive
     * @return Array
     * (
     *         whether the input is valid? //bool
     *         need refresh? //bool
     * )
     */
    public function validateAndReset($input)
    {
        $code = $this->getVerifyCode();
        $valid = ($input === $code);
        $this->getVerifyCode(true);
        return $valid;
    }
    /**
     * Gets the verification code.
     * @param string $regenerate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerifyCode($regenerate=false)
    {
        if($this->fixedVerifyCode !== null)
            return $this->fixedVerifyCode;
        $session = Revship::session();
        $name = $this->getSessionKey();
        if($session->get($name) === false || $regenerate)
        {
            $session->set($name, $this->generateVerifyCode());
        }
        return $session->get($name);
    }
    /**
     * Generates a new verification code.
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        return md5(microtime().rand());
    }
    /**
     * Returns the session variable name used to store verification code.
     * @return string the session variable name
     */
    protected function getSessionKey()
    {
        return self::SESSION_KEY ;
    }
    
}
