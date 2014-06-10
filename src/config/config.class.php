<?php
/** 
 * @author SLJ
 */
Revship::getLibClass('revship.config.abstract');
class Revship_Config
{
    const CONF_TYPE_MYSQL = 'mysql';
    const CONF_TYPE_FILE = 'file';
    
    const KEY_TYPE = 'type';
    const KEY_KEY = 'key'; 
    
    protected $instanceObj = null;
    // Set default type
    const CONF_TYPE_DEFAULT = self::CONF_TYPE_FILE;
    
    
    public function createInstance($type = self::CONF_TYPE_DEFAULT)
    {
        switch ( $type )
        {
            case self::CONF_TYPE_FILE:
                $this->instanceObj = Revship::lib('config.driver.file');
                break;
                /*
            case self::CONF_TYPE_MYSQL:
                $this->instanceObj = Revship::lib('config.driver.mysql');
                break;
                */
            default:
                break;
        }
        return $this;
    }
    public function getItem($configNamespace)
    {
        $configType = self::getConfigTypeByNamespace($configNamespace);
        $configKey = self::getConfigKeyByNamespace($configNamespace);
        return $this->instanceObj->getItem($configType, $configKey);
    }
    public function setSingleItem($configNamespace, $newValue)
    {
        $configType = self::getConfigTypeByNamespace($configNamespace);
        $configKey = self::getConfigKeyByNamespace($configNamespace);
        return $this->instanceObj->setSingleItem($configType, $configKey, $newValue);
    }
    public function setItems($configType, $keyValues)
    {
        return $this->instanceObj->setItems($configType, $keyValues);
    }
    public function returnAllConfigArray()
    {
        return $this->instanceObj->returnAllConfigArray();
    }
    
    public function clearCache( $configType = null )
    {
        if($configType == null)
        {
            $this->configArray = array();
        }
        else
        {
            unset($this->configArray[$configType]);
        }
        return true;
    }
    
    /**
     * Get config type from namespace
     * @example theme.dirname > theme
     * @param string $configNamespace
     */
    public static function getConfigTypeByNamespace($configNamespace)
    {
        $configPos = self::namespaceExplode($configNamespace);
        return $configPos[self::KEY_TYPE];
    }
    /**
     * Get config key from namespace
     * @example theme.dirname > dirname
     * @param string $configNamespace
     */
    public static function getConfigKeyByNamespace($configNamespace)
    {
        $configPos = self::namespaceExplode($configNamespace);
        return $configPos[self::KEY_KEY];
    }
    /**
     * Get config filename from namespace
     * @example theme.dirname > theme.php
     * @param string $configNamespace
     */
    public static function getConfigFileNameByNamespace($configNamespace)
    {
        $configPos = self::namespaceExplode($configNamespace);
        return $configPos[self::KEY_TYPE_FILE_NAME];
    }
    /**
     * Make namespace to filename and array key
     *  theme.dirname
     *  = array(
     *  ['typeFileName'] => 'theme.php' // filename
     *  ['type'] => 'theme' // type
     *  ['key'] => 'dirname' // key
     * )
     * @param unknown_type $configNamespace
     */
    public static function namespaceExplode($configNamespace)
    {
        $configPos = explode('.', $configNamespace, 2);
        if(strpos($configNamespace, '.') === false)
            $configPos[1]='';
        return array(
                      self::KEY_TYPE => $configPos[0],
                      self::KEY_KEY => $configPos[1],
                    );
    } 
    
}
