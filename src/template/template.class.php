<?php

defined('REVSHIP') or exit('Access Denied!');
/**
 * Revship Template Class
 * @author SLJ
 */

if (!file_exists( LIB_PATH . 'smarty' . DS . 'Smarty.class.php'))
{
    new Revship_Exception(404, 'Unable to load lib: ' . LIB_PATH . 'smarty' . DS . 'Smarty.class.php', E_USER_ERROR);
}

require_once(LIB_PATH . 'smarty' . DS . 'Smarty.class.php');

class Revship_Template extends Smarty  {

    protected $applicationPluginPath = null;

    public function __construct($params = array())
    {
        parent::__construct();

        $this->applicationPluginPath = APP_PATH . 'plugin' . DS . 'smarty';
        
        if(isset($params['plugins_dir']))
        {
            $this->plugins_dir[] =  $params['plugins_dir'] . DS; 
        }
        $this->plugins_dir[] =  $this->applicationPluginPath . DS;
        //$smarty->force_compile = true;
        //$this->debugging = $this->debugging;// Revship::lib('config')->getItem('theme.smarty.debugging') ? Revship::lib('config')->getItem('theme.smarty.debugging') : false;
        //$this->_oSmarty->caching = $this->caching;// Revship::lib('config')->getItem('theme.smarty.caching') ? Revship::lib('config')->getItem('theme.smarty.caching') : true;
        //$this->_oSmarty->cache_lifetime = $this->cache_liftime;// Revship::lib('config')->getItem('theme.smarty.cache_lifetime') ? Revship::lib('config')->getItem('theme.smarty.cache_lifetime') : 120;
        if(isset($params['template_dir']))
        {
            $this->template_dir = $params['template_dir'] . DS ;
        }
        else
        {
            $theme = Revship::lib('config')->getItem('theme.dirname');
            if( ! $theme )
            {
                new Revship_Exception(404, 'Setting does not exist [theme.dirname]');
            }
            if( ! file_exists( THEME_PATH . $theme ) )
            {
                new Revship_Exception(404, 'Theme directory [ '.THEME_PATH.$theme.' ] does not exist.');
            }
            $this->template_dir = THEME_PATH . $theme . DS;
        }
        $this->compile_dir = SITE_PATH . 'compile' ;
        $this->compile_check = true;    
        $this->left_delimiter = '{{';
        $this->right_delimiter = '}}';
    }
}
