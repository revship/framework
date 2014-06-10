<?php

if ( ! defined('REVSHIP')) exit('No direct script access allowed');
/**
 * Revship Basic Class.
 * 
 * This is the basic class of Revship.\n
 * This class includes reference of some factory pattern.(like db, session etc.) \n
 * This class parse URI and route to corresponding controller and method.\n
 * This class also stores class instance, \n
 * so that when user call Revship::lib('config')->someFunc() will not re-new a config class.
 * 
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 *
 */  
final class Revship
{
    /**
     * Kernel Version
     */
    const VERSION = '1.0';
    const PACKAGE_BASIC = 1;
    const PACKAGE_PREMIUM = 2;
    const PACKAGE_ULTIMATE = 3;
    /**
     * Loaded Class Storage.
     */
    public static $_objects = array();
    
    public static function getPackage()
    {
        // @todo return current package
        return 3;
    }
    public static function getPackageName($package = null)
    {
        if($package == null)
        {
            $package = self::getPackage();
        }
        switch ($package)
        {
            case self::PACKAGE_BASIC:
                return 'Basic';
                break;
            case self::PACKAGE_PREMIUM:
                return 'Premium';
                break;
            case self::PACKAGE_ULTIMATE:
                return 'Ultimate';
                break;
            default:
                return 'Unknown';
                break;
        }
    }
    /**
     * Initialize language, session etc.
     */
    public static function init()
    {
        //language init
        self::l('');
        //session_start
        self::lib('session.handler')->getInstance()->init();
        // Check Installed?
        self::checkInstalled();
        self::setTimezone();
    }
    private static function setTimezone()
    {
        $timezone = Revship::lib('config')->getItem('site.timezone');
        if($timezone)
        {
            self::lib('datetime')->setTimezone($timezone); 
        }
    }
    private static function checkInstalled()
    {
        // Config lock is not set, so not installed, if not IN_INSTALLATION, then redirect
        if( ! self::lib('config')->getItem('install.lock') && !defined('IN_INSTALLATION') )
        {
           Revship_Http::redirect('/install/install.php'); 
        }
    }
    /**
     * Run a Revship Application.
     * 
     * This function calls only one time by bootstrap.\n
     * Every request will only run the application once.\n
     * This function helps to parse URI, load corresponding controller, and call its Action function.
     */
    public static function run()
    {
        self::init();
        //Revship_Licenser::checkAndRedirectToConfigDomain();
        //Route URI
        self::lib('router')->_set_routing();
        //Load Controller by URI
        self::lib('controller');
        //Verify Controller by URI
        if ( file_exists(APP_PATH.'base'.DS.'controller'.DS.self::lib('router')->fetch_directory().self::lib('router')->fetch_class().'BaseController.php'))
        {
            require( APP_PATH . 'base' . DS . 'controller' . DS . self::lib('router')->fetch_directory() . self::lib('router')->fetch_class() . 'BaseController.php' );
            $controllerClass = self::lib('router')->fetch_class().'BaseController';
        }
        if ( file_exists(APP_PATH.'controller'.DS.self::lib('router')->fetch_directory().self::lib('router')->fetch_class().'Controller.php'))
        {
            require( APP_PATH . 'controller' . DS .  self::lib('router')->fetch_directory() . self::lib('router')->fetch_class() . 'Controller.php' );
            $controllerClass = self::lib('router')->fetch_class().'Controller';
            //////new Revship_Exception(404,'Unable to load your default controller [ '. self::lib('router')->fetch_directory().self::lib('router')->fetch_class() .'Controller ]. Please make sure the controller specified in \'application/config/routes.php\' is valid.');
        }
        
        $controllerObj = new $controllerClass;
        $actionMethod = self::lib('router')->fetch_method().'Action';
         if(method_exists($controllerObj,$actionMethod))
        {
            $controllerObj->$actionMethod();
        }
        else
        {
            self::showError(404, 'Not found method [ '.$actionMethod.' ] in controller [ '.$controllerClass.' ]');
        }
    }
    
    public static function showError($errorCode=404, $msg='')
    {
        if ( file_exists(APP_PATH.'base'.DS.'controller'.DS.self::lib('router')->fetch_directory().'errorBaseController.php'))
        {
            require( APP_PATH . 'base' . DS . 'controller' . DS . self::lib('router')->fetch_directory() . 'errorBaseController.php' );
            $controllerClass = 'errorBaseController';
        }
        if ( file_exists(APP_PATH.'controller'.DS.self::lib('router')->fetch_directory().'errorController.php'))
        {
            require( APP_PATH . 'controller' . DS .  self::lib('router')->fetch_directory() . 'errorController.php' );
            $controllerClass = 'errorController';
        }
        
        $controllerObj = new $controllerClass;
        $actionMethod = 'show'.$errorCode;
         if(method_exists($controllerObj,$actionMethod))
        {
            Revship_Http::setStatusHeader($errorCode);
            $controllerObj->$actionMethod();
        }
        else
        {
            new Revship_Exception($errorCode,$msg);
        }
    }
    
    /**
     * Reference for db handler.
     * 
     * This is a shortcut to Revship::lib('database')->createDb() \n
     * So actually it calls Revship_Database::createDb()
     * @see Revship_Database::createDb()
     * @return object
     */
    public static function db()
    {
        return self::lib('database')->createDb();
    }
    public static function apc()
    {
        $config = self::lib('config')->getItem('apc');
        if(empty($config))
        {
            return false;
        }
        return self::lib('cache',$config)->getInstance();
    }
    public static function memcache()
    {
        $config = self::lib('config')->getItem('memcache');
        if(empty($config))
        {
            return false;
        }
        return self::lib('cache',$config)->getInstance();
    }
    public static function redis()
    {
        $redisConfig = self::lib('config')->getItem('redis');
        if(empty($redisConfig))
        {
            return false;
        }
        return self::lib('cache',$redisConfig)->getInstance();
    }
    /**
     * Reference for session handler.
     * 
     * This is a shortcut to Revship::lib('session')->getInstance() \n
     * So actually it calls Revship_Session::getInstance()
     * @see Revship_Session::getInstance()
     * @return object
     */
    public static function session()
    {
        return self::lib('session')->getInstance();
    }
    
    /**
     * Transform phrase to target language.
     *  
     * This is a shortcut to Revship_Language::transformPhrase()
     * 
     * @param string $phrase  Original Text
     * @param array $replaceArray  Replace Array
     * @param string $namespace   system / geo / custom
     * @return string
     * @see Revship_Language::transformPhrase() for detailed php/smarty calling method.
     */
    public static function l($phrase, $replaceArray = null, $namespace = 'system')
    {
        return self::lib('language')->transformPhrase($phrase, $replaceArray, $namespace);
    }
    /**
     * Load Config
     * @param $siteId   Identity for site.  
     * If $siteId is null, it will read file configs. Otherwise, it reads DB
     * 
     * Please call $this->smartConfGet(...) on your controller subclasses, 
     * which will auto get siteId and try to get site config(db),
     * and if site config is not found, it will get global config(file)
     */
    public static function conf()
    {
        self::lib('config');
        // App settings (from local file)
        return self::lib('config')->createInstance(Revship_Config::CONF_TYPE_FILE);
    }
    /**
     * Load a model.
     * 
     * This function automatically includes Revship_Model and Revship_Model_Form \n
     * Then it checked if this model loaded before.\n
     * If not, it loads and return the newed object.\n
     * If yes, it directly return the stored object.
     * 
     * @param string $modelName
     * @param array $params
     */
    public static function &model($modelName,$params =array())
    {
        if($modelName=='') new Revship_Exception(501, 'No model specified.');
        self::getLibClass('revship.model');
        self::getLibClass('revship.model.form');
        self::getModelClass($modelName);
        $hash=self::classNameToHash($modelName, $params);
        self::$_objects[$hash] = self::getObj($modelName, $params);
        return self::$_objects[$hash];
    }
    /**
     * Load an extension.
     * 
     * This function automatically includes Revship_Extension. \n
     * Then it checked if this extension loaded before.\n
     * If not, it loads and return the newed object.\n
     * If yes, it directly return the stored object.
     * 
     * @param string $extensionName
     * @param array $params
     */
    public static function &ext($extensionName,$params =array())
    {
        if($extensionName=='') new Revship_Exception(501, 'No extension specified.');
        self::getLibClass('revship.extension');
        self::getExtensionClass($extensionName);
        $hash=self::classNameToHash($extensionName, $params);
        self::$_objects[$hash] = self::getObj($extensionName.'Extension', $params);
        return self::$_objects[$hash];
    }

    public static function isDebug()
    {
        return self::lib('config')->getItem('site.debug');
    }
    
    /**
     * Load a root Library.
     * 
     * This method allow you to load library besides Revship.\n
     * Revship::rootLib('abc.def')\n
     * will load /include/library/abc/def.class.php
     * 
     * @param string $libName
     * @param array $params
     * @return object
     */
    public static function &rootLib($libName, $params =array())
    {
        // Load Class to $_objects
        self::getLibClass($libName);
        $hash=self::classNameToHash($libName, $params);
        self::$_objects[$hash] = self::getObj($libName, $params);
        return self::$_objects[$hash] ;
    }
    public static function log($msg, $type='DEBUG')
    {
        self::lib('log')->log($msg, $type);
    }
    /**
     * Load a Revship Library.
     * 
     * Revship::lib('mail') \n
     * the same as Revship::rootLib('revship.mail') \n
     * will load class Revship_Mail located in library/revship/mail/mail.class.php \n
     * @param string $libName
     * @return object
     */
    public static function &lib($libName, $params = array())
    {
        // mail => revship.mail
        // gateway.paypal => revship.gateway.paypal
        return self::rootLib('revship.'.$libName, $params);
    }
    /**
     * Load a Library Class
     * 
     * First it turns class namespace into path \n
     * - revship.archive.export => revship/archive/export \n
     * 
     * Then it loads the file \n
     * -# revship.archive => library/revship/archive.class.php \n
     * -# revship.archive => library/revship/archive/archive.class.php (when #1 file not exist) \n
     * -# revship.archive.export => library/revship/archive/export.class.php  \n
     * 
     * getLibClass('revship.mail') \n
     * getLibClass('revship.file.upload') \n
     *
     * @param string $className
     * @return bool Returns true when class loaded.
     * @exception throw exception when it cannot find the file.
     */
    public static function getLibClass($classNamespace)
    {
        /*
         * Turn class namespace into path
         * revship.archive.export => revship/archive/export
         */
        $className = self::upperCaseClassName($classNamespace);
        $classPath = trim(strtolower(str_replace('.', DS, $classNamespace)));
        // Already required. Return true
        if (isset(self::$_objects[$className]))
        {
            return true;
        }
        
        /*
         * File of class
         * revship.archive => library/revship/archive.class.php (not exist)
         * revship.archive.export => library/revship/archive/export.class.php
         */
        $fileName1 = LIB_PATH . $classPath . '.class.php';
        if (file_exists($fileName1))
        {
            //Skip if class loaded by static method already
            if( ! class_exists( $className ) )
            {
                require($fileName1);
            }
            self::$_objects[$className] = md5($className);
            return true;
        }
        /*
         * If load main class
         * revship.archive => library/revship/archive/archive.class.php
         */
        $fileName2='';
        $parts = explode(DS, $classPath);      
        $partsNum = count($parts);
        if ($partsNum>1)
        {
            $fileName2 = LIB_PATH . $classPath . DS . $parts[$partsNum-1] . '.class.php';           
            if (file_exists($fileName2))
            {
                if( ! class_exists( $className ) )
                {
                    require($fileName2);
                }
                self::$_objects[$className] = md5($className);
                return true;
            }
        }  
        /*
         * Cannot find the class filename. Call exception
         */
        new Revship_Exception(501, 'Unable to load class: [ ' . $className . ' ] which should be located in [ ' . $fileName1 .' ] or [' . $fileName2 .' ]', E_USER_ERROR);
        return false;
    }
    
    private static function getModelClass($modelName)
    {
        $modelName = ucfirst($modelName);
        // Already required. Return true
        if (isset(self::$_objects[$modelName]))
        {
            return true;
        }
        /*
         * File of class
         * Business => application/base/model/BusinessBase.php
         * and the customized
         *          => application/model/Business.php
         */
        $baseFileName =  APP_PATH . 'base' . DS . 'model' . DS . $modelName . 'Base.php' ;
        $customFileName =  APP_PATH . 'model' . DS . $modelName . '.php' ;
        if( file_exists($baseFileName) )
        {
            require($baseFileName);
        }
        if( file_exists($customFileName) )
        {
            require($customFileName);
            self::$_objects[$modelName] = md5($modelName);
            return true;
        }
        /*
         * Cannot find the class filename. Call exception
         */
        new Revship_Exception(501, 'Unable to load model: [ ' . $modelName . ' ] which should be located in [ ' . $customFileName .' ] ', E_USER_ERROR);
        return false;
    }
    private static function getExtensionClass($extensionName)
    {
        // Already required. Return true
        if (isset(self::$_objects[$extensionName]))
        {
            return true;
        }
        /*
         * File of class
         * theme => application/extension/themeExtension.php
         */
        $customFileName =  EXT_PATH . DS . $extensionName . 'Extension.php' ;
        if( file_exists($customFileName) )
        {
            require($customFileName);
            self::$_objects[$extensionName] = md5($extensionName);
            return true;
        }
        /*
         * Cannot find the class filename. Call exception
         */
        new Revship_Exception(501, 'Unable to load extension: [ ' . $extensionName . ' ] which should be located in [ ' . $customFileName .' ] ', E_USER_ERROR);
        return false;
    }
    
    /**
     * Get or create an object for a class
     *
     * @return object Return object after new
     * @param string $className
     * @param array $classParams
     */
    public static function &getObj($className, $classParams = array())
    {
        $hash = self::classNameToHash($className, $classParams);
        if (isset(self::$_objects[$hash]))
        {
            return self::$_objects[$hash];
        }
        $className = str_replace(array('.', '-'), '_', $className);    
        
        if (!class_exists($className))
        {
            throw new Revship_Exception(501,'Existed Class: ' . $className, E_USER_ERROR);
        }      
        if ($classParams)
        {
            self::$_objects[$hash] = new $className($classParams);
        }
        else
        {      
            self::$_objects[$hash] = new $className();
        }
        return self::$_objects[$hash];
    }
    /**
     * Make class name and params to to a md5 hash string
     *  
     * @param string $className
     * @param array $classParams
     */
    private static function classNameToHash($className, $classParams = array())
    {
        return md5($className . serialize($classParams));
    }
    /**
     * Upper Case the Class Name.
     * 
     * revship_mail => Revship_Mail \n
     * @param string $classNamespace
     * @return string
     */
    private static function upperCaseClassName($classNamespace)
    {
        $className = trim(str_replace('.', '_', $classNamespace));
        $parts = explode('_', $className);
        if(!function_exists('map_parts_upper_case_class_name'))
        {
            function map_parts_upper_case_class_name(&$value)
            {
                return ucfirst(strtolower($value));
            }
        }
        $parts = array_map("map_parts_upper_case_class_name",$parts);
        return implode('_',$parts);
    }
    /**
     * End the application.
     * 
     * Do NOT use die() or exit() in production environment. \n
     * Please use Revship::end() to exit application.
     * @param string $message
     */
    public static function end($message = null)
    {
        //@todo Revship::end()
        die($message);
    }
}

final class Revship_Licenser
{
    public static function checkLicenseLocally()
    {
        $realDomain = self::getRealDomain();
        $configDomain = self::getConfigDomain();
        if( $realDomain != $configDomain )
        {
            return false;
        }
        if( self::genLicenseDomain($realDomain) != self::genLicenseDomainFromRealDomain() )
        {
            return false;
        }
        return true;
    }
    /**
     * Check if the license in store/in param is valid on line
     * @param string $licenseNumber (optional)
     */
    public static function checkLicenseOnline($licenseNumber = null)
    {
        require_once(MANAGE_PATH . 'util/ManageService.class.php');
        $serviceStatus = ManageService::getServiceStatus($licenseNumber);
        $valid = (isset($serviceStatus['success']) 
                           && $serviceStatus['success']==1 
                           && $serviceStatus['domain']==Revship_Licenser::genLicenseDomainFromRealDomain()
                           ) ? true:null;
        //Piracy
        if( isset($serviceStatus['piracy']) && $serviceStatus['piracy'] ==1 )
        {
            return false;
        }
        return $valid;
    }
    /**
     * Get Real Domain from $_SERVER['HTTP_HOST'];
     */
    public static function getRealDomain()
    {
        return $_SERVER['HTTP_HOST'];
    }
    /**
     * Get Config Domain
     */
    public static function getConfigDomain()
    {
        return Revship::lib('config')->getItem('site.domainUrl');
    }
    /**
     * Check and redirect to config domain
     */
    public static function checkAndRedirectToConfigDomain()
    {
        $configDomain = self::getConfigDomain();
        $realDomain = self::getRealDomain();
        $protocol = Revship::lib('config')->getItem('site.domainProtocol');
        if(empty($protocol)) 
        {
            $protocol = 'http';
        }
        if( $configDomain != $realDomain  )
        {
            Revship::lib('http')->redirectPermently($protocol . '://'.$configDomain.$_SERVER['REQUEST_URI']);
        }
    }
    /**
     * Generate License Domain from  $_SERVER['HTTP_HOST']
     */
    public static function genLicenseDomainFromRealDomain()
    {
        $domain=$_SERVER['HTTP_HOST'];
        return self::genLicenseDomain($domain);
    }
    /**
     * Format to a license domain from a domain
     * @param string $domain
     * @return string 
     * @example  www.abc.com -> abc.com
     * @example  def.abc.com -> def.abc.com
     */
    public static function genLicenseDomain($domain)
    {
        if(strtolower(substr($domain,0,4))=='www.')
        {
            //Domain started with www.
            $domain = substr($domain,4);
        }
        return $domain;
    }
}
