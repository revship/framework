<?php
/** 
 * @author SLJ
 * 
 * 
 */
abstract class Revship_Config_Abstract
{
    abstract public function getItem($configType, $configKey);

    /**
     * Update single config item and write to config file
     * 
     * @example  Revship::lib('config')->setSingleItem('site.domain', 'revship.com' )
     */
    abstract public function setSingleItem($configType, $configKey, $newValue);

    /**
     * Update configs and write to config file
     * 
     * @example  Revship::lib('config')->setItems('site', array('domain'=>'revship.com', 'license'=>'1234' ))
     */
    abstract public function setItems($configType, $keyValues);

    abstract public function returnAllConfigArray();

}
