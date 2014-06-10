<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Extension
{
    protected $_user = null;
    public function __construct(){
        $this->_user = Revship::lib('auth')->getUser();
    }
}