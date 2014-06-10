<?php
/** 
 * @author SLJ
 */

class Revship_Config_Driver_File extends Revship_Config_Abstract
{
    protected $configArray = array();
    
    public function getItem($configType, $configKey=null)
    {
        if( ! array_key_exists( $configType, $this->configArray ) )
        {
            $this->loadConfigFileByNamespace($configType, $configKey);
        }
        
        if( array_key_exists ( $configType, $this->configArray ) )
        {
            if(!$configKey && $configType)
                return $this->configArray[$configType];
            else if($configKey && array_key_exists($configKey,$this->configArray[$configType]))
                return $this->configArray[$configType][$configKey];
            else
                return null;
        }
    }
    /**
     * Update single config item and write to config file
     * 
     * @example  Revship::lib('config')->setSingleItem('site.domain', 'revship.com' )
     */
    public function setSingleItem($configType, $configKey, $newValue)
    {
        $this->getItem($configType);
        $this->configArray[$configType][$configKey] = $newValue;
        $this->saveConfigFileByNamespace($configType, $configKey);
    }
    /**
     * Update configs and write to config file
     * 
     * @example  Revship::lib('config')->setItems('site', array('domain'=>'revship.com', 'license'=>'1234' ))
     */
    public function setItems($configType, $keyValues)
    {
        if( ! is_array($keyValues) )
        {
            throw new Revship_Exception(501, __METHOD__);
        }
        $this->getItem($configType);
        foreach ($keyValues as $configKey => $newValue)
        {
            $this->configArray[$configType][$configKey] = $newValue;
        }
        $this->saveConfigFileByNamespace($configType);
    }
    public function returnAllConfigArray()
    {
        $list = Revship::lib('file')->getFiles( CONFIG_PATH, '.php');
        foreach ($list as $item)
        {
            $item = str_replace('.php', '', $item);
            $this->getItem($item);
        }
        return $this->configArray;
    }
    /**
     * Load config file
     * @param string $configNamespace
     * @throws Revship_Exception
     */
    protected function loadConfigFileByNamespace($configType, $configKey) 
    {
        $configTypeFileName = $configType . '.php';
        if(is_string($configTypeFileName))
        {
            if(file_exists( CONFIG_PATH . $configTypeFileName ))
            {
                @$array = require( CONFIG_PATH . $configTypeFileName );
                if(isset($array) && is_array($array))
                {
                    $this->configArray[$configType]=$array;
                }
                else
                {
                    $this->configArray[$configType]=array();
                }
            }
            else 
            {
                $this->configArray[$configType]=array();
            }
        }
    }
    protected function saveConfigFileByNamespace($configType, $configKey=null)
    {
        $configTypeFileName = $configType . '.php';
        // file not writable?
        $fileName = CONFIG_PATH . $configTypeFileName;
        if( file_exists($fileName) && ! Revship::lib('file')->isWritable(  $fileName  )  )
        {
            throw new Revship_Exception(502, "File is not writable:". CONFIG_PATH . $configTypeFileName);
        }
        if(  ! Revship::lib('file')->isWritable( CONFIG_PATH  ) )
        {
            throw new Revship_Exception(502, "Folder is not writable:". CONFIG_PATH );
        }
        // build content
        $content = "<?php \nreturn array(\n";
        
        foreach($this->configArray[$configType] as $key=>$value )
        {
            $k = $key; // addslashes($key);
            if(is_numeric($value))
            {
                $content .= " '{$k}' => {$value}, \n";
            }
            else if(is_bool($value))
            {
                if($value)
                {
                    $v = 'true';
                }
                else
                {
                    $v = 'false';
                }
                $content .= " '{$k}' => {$v}, \n";
            }
            else
            {
                $v = $value;// addslashes($value);
                $content .= " '{$k}' => '{$v}', \n";
            }
        }
        $content .= ");";
        //write to file
        Revship::lib('file')->write(CONFIG_PATH . $configTypeFileName, $content);
    }
}
