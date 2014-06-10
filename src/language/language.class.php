<?php
/*
 * Revship Language Class 
 * example:
 * In PHP:
 *   -Code
 *    Revship::l('Hello!')
 *       -Result (maybe another language)
 *        Hello!
 *   -Code
 *    Revship::l('Welcom to %s!', 'Revship')
 *       -Result (maybe another language)
 *        Welcome to Revship!
 *   -Code
 *    Revship::l('They are %s, %s and %s.',array('dog','cat','pig'))
 *       -Result (maybe another language)
 *        They are dog, cat and pig.
 *        
 * In Template HTML
 *   -Code
 *    {{l t='Hello!'}}
 *       -Result (maybe another language)
 *        Hello!
 *    {{l t='Welcom to %s!' r='Revship'}}
 *       -Result (maybe another language)
 *        Welcome to Revship!
 *    {{l t='They are %s, %s and %s.' r1='dog' r2='cat' r3='pig'}}
 *       -Result (maybe another language)
 *        They are dog, cat and pig.
 * 
 * @author SLJ
 *
 */
class Revship_Language
{
    /*
     * Want load lang code
     */
    protected $languageCode;
    /*
     * Language directory
     */
    protected $languagePath;
    /*
     * Language pack file location and filename
     */
    protected $languagePackFile;
    /*
     * Default lang code
     */
    protected $defaultLanguageCode = 'en-us';
    /*
     * Language phrases array in pack file
     */
    protected $phrases = array();
    /*
     * Language Codes
     * http://msdn.microsoft.com/en-us/library/ms533052(v=vs.85).aspx
     */
    protected $codeNames = array(
    'af' => array('Afrikaans', '', ''), 
    'sq' => array('Albanian', '', ''), 
    'ar-sa' => array('Arabic', 'SA', 'Saudi Arabia'), 
    'ar-iq' => array('Arabic', 'IQ', 'Iraq'), 
    'ar-eg' => array('Arabic', 'EG', 'Egypt'), 
    'ar-ly' => array('Arabic', 'LY', 'Libya'), 
    'ar-dz' => array('Arabic', 'DZ', 'Algeria'), 
    'ar-ma' => array('Arabic', 'MA', 'Morocco'), 
    'ar-tn' => array('Arabic', 'TN', 'Tunisia'), 
    'ar-om' => array('Arabic', 'OM', 'Oman'), 
    'ar-ye' => array('Arabic', 'YE', 'Yemen'), 
    'ar-sy' => array('Arabic', 'SY', 'Syria'), 
    'ar-jo' => array('Arabic', 'JO', 'Jordan'), 
    'ar-lb' => array('Arabic', 'LB', 'Lebanon'), 
    'ar-kw' => array('Arabic', 'KW', 'Kuwait'), 
    'ar-ae' => array('Arabic', 'AE', 'U.A.E.'), 
    'ar-bh' => array('Arabic', 'BH', 'Bahrain'), 
    'ar-qa' => array('Arabic', 'QA', 'Qatar'), 
    'eu' => array('Basque', '', ''), 
    'bg' => array('Bulgarian', '', ''), 
    'be' => array('Belarusian', '', ''), 
    'ca' => array('Catalan', '', ''), 
    'zh-tw' => array('Chinese', 'TW', 'Taiwan', '繁體中文'), 
    'zh-cn' => array('Chinese', 'CN', 'PRC', '简体中文'), 
    'zh-hk' => array('Chinese', 'HK', 'Hong Kong SAR', '繁體中文'), 
    'zh-sg' => array('Chinese', 'SG', 'Singapore', '简体中文'), 
    'hr' => array('Croatian', '', ''), 
    'cs' => array('Czech', '', ''), 
    'da' => array('Danish', '', ''), 
    'nl' => array('Dutch', '', 'Standard'), 
    'nl-be' => array('Dutch', 'BE', 'Belgium'), 
    'en' => array('English', '', ''), 
    'en-us' => array('English', 'US', 'United States'), 
    'en-gb' => array('English', 'GB', 'United Kingdom'), 
    'en-au' => array('English', 'AU', 'Australia'), 
    'en-ca' => array('English', 'CA', 'Canada'), 
    'en-nz' => array('English', 'NZ', 'New Zealand'), 
    'en-ie' => array('English', 'IE', 'Ireland'), 
    'en-za' => array('English', 'ZA', 'South Africa'), 
    'en-jm' => array('English', 'JM', 'Jamaica'), 
    'en' => array('English', '', 'Caribbean'), 
    'en-bz' => array('English', 'BZ', 'Belize'), 
    'en-tt' => array('English', 'TT', 'Trinidad'), 
    'et' => array('Estonian', '', ''),
    'fo' => array('Faeroese', '', ''), 
    'fa' => array('Farsi', '', ''), 
    'fi' => array('Finnish', '', ''), 
    'fr' => array('French', '', 'Standard', 'Français'), 
    'fr-be' => array('French', 'BE', 'Belgium', 'Français'), 
    'fr-ca' => array('French', 'CA', 'Canada', 'Français'), 
    'fr-ch' => array('French', 'CH', 'Switzerland', 'Français'), 
    'fr-lu' => array('French', 'LU', 'Luxembourg', 'Français'), 
    'gd' => array('Gaelic', '', 'Scotland'),
    'ga' => array('Irish', '', ''), 
    'de' => array('German', '', 'Standard'), 
    'de-ch' => array('German', 'CH', 'Switzerland'), 
    'de-at' => array('German', 'AT', 'Austria'), 
    'de-lu' => array('German', 'LU', 'Luxembourg'), 
    'de-li' => array('German', 'LI', 'Liechtenstein'), 
    'el' => array('Greek', '', ''), 
    'he' => array('Hebrew', '', ''), 
    'hi' => array('Hindi', '', ''), 
    'hu' => array('Hungarian', '', ''), 
    'is' => array('Icelandic', '', ''), 
    'id' => array('Indonesian', '', ''), 
    'it' => array('Italian', '', 'Standard'), 
    'it-ch' => array('Italian', 'CH', 'Switzerland'), 
    'ja' => array('Japanese', '', '','日本語'), 
    'ko' => array('Korean', '', ''), 
    'ko' => array('Korean', '', 'Johab'), 
    'lv' => array('Latvian', '', ''), 
    'lt' => array('Lithuanian', '', ''), 
    'mk' => array('Macedonian', '', 'FYROM'), 
    'ms' => array('Malaysian', '', ''), 
    'mt' => array('Maltese', '', ''), 
    'no' => array('Norwegian', '', ''), 
    'pl' => array('Polish', '', ''), 
    'pt-br' => array('Portuguese', 'BR', 'Brazil'), 
    'pt' => array('Portuguese', '', 'Portugal'), 
    'rm' => array('Rhaeto-Romanic', ''), 
    'ro' => array('Romanian', '', ''), 
    'ro-mo' => array('Romanian', 'MO', 'Republic of Moldova'), 
    'ru' => array('Russian', '', ''), 
    'ru-mo' => array('Russian', 'MO', 'Republic of Moldova'), 
    'sz' => array('Sami', '', 'Lappish'), 
    'sr' => array('Serbian', '', 'Cyrillic'), 
    'sr' => array('Serbian', '', 'Latin'), 
    'sk' => array('Slovak', '', ''), 
    'sl' => array('Slovenian', '', ''), 
    'sb' => array('Sorbian', '', ''), 
    'es' => array('Spanish', '', 'Spain', 'Español'), 
    'es-mx' => array('Spanish', 'MX', 'Mexico', 'Español'), 
    'es-gt' => array('Spanish', 'GT', 'Guatemala', 'Español'), 
    'es-cr' => array('Spanish', 'CR', 'Costa Rica', 'Español'), 
    'es-pa' => array('Spanish', 'PA', 'Panama', 'Español'), 
    'es-do' => array('Spanish', 'DO', 'Dominican Republic', 'Español'), 
    'es-ve' => array('Spanish', 'VE', 'Venezuela', 'Español'), 
    'es-co' => array('Spanish', 'CO', 'Colombia', 'Español'), 
    'es-pe' => array('Spanish', 'PE', 'Peru', 'Español'), 
    'es-ar' => array('Spanish', 'AR', 'Argentina', 'Español'), 
    'es-ec' => array('Spanish', 'EC', 'Ecuador', 'Español'), 
    'es-cl' => array('Spanish', 'CL', 'Chile', 'Español'), 
    'es-uy' => array('Spanish', 'UY', 'Uruguay', 'Español'), 
    'es-py' => array('Spanish', 'PY', 'Paraguay', 'Español'), 
    'es-bo' => array('Spanish', 'BO', 'Bolivia', 'Español'), 
    'es-sv' => array('Spanish', 'SV', 'El Salvador', 'Español'), 
    'es-hn' => array('Spanish', 'HN', 'Honduras', 'Español'), 
    'es-ni' => array('Spanish', 'NI', 'Nicaragua', 'Español'), 
    'es-pr' => array('Spanish', 'PR', 'Puerto Rico', 'Español'), 
    'sx' => array('Sutu', '', ''), 
    'sv' => array('Swedish', '', ''), 
    'sv-fi' => array('Swedish', 'FI', 'Finland'), 
    'th' => array('Thai', '', ''), 
    'ts' => array('Tsonga', '', ''), 
    'tn' => array('Tswana', '', ''), 
    'tr' => array('Turkish', '', ''), 
    'uk' => array('Ukrainian', '', ''), 
    'ur' => array('Urdu', '', ''), 
    've' => array('Venda', '', ''), 
    'vi' => array('Vietnamese', '', ''), 
    'xh' => array('Xhosa', '', ''), 
    'ji' => array('Yiddish', '', ''), 
    'zu' => array('Zulu', '', '')
    );
    
    
    public function __construct()
    {
        /*
         * Set languagePath
         */
        $this->languagePath = APP_PATH.'language' . DS;
        /*
         * Set languageCode
         */
        $this->initDefaultLanguage();
        /*
         * Set languagePackFile
         */
        $this->initLanguagePack();
        
    }
    /**
     * Get Exsited Language List
     */
    public function getExistedLanguageList()
    {
        $list = Revship::lib('file')->getFiles($this->languagePath, '.lang.php');
        foreach ($list as & $item)
        {
            $item = str_replace('.lang.php', '', $item);
        }
        return $list;
    
    }
    protected function initLanguagePack()
    {
        /*
         * Load lang pack which user wants if file exists
         */
        if(file_exists(strtolower($this->languagePath . $this->languageCode . ".lang.php" )))
        {
            $this->languagePackFile = $this->languagePath . $this->languageCode . ".lang.php";
        }
        /*
         * If lang pack is not exist, load if english pack exists
         */
        else if(file_exists(strtolower($this->languagePath . $this->defaultLanguageCode . ".lang.php" )))
        {
            $this->languagePackFile = $this->languagePath . $this->defaultLanguageCode . ".lang.php";
        }
        else
        {
            $this->languagePackFile = null;
        }
    }
    
    /*
    Init Default Language
    */
    protected function initDefaultLanguage()
    {
        /*
         * If first visit with no cookie.
         * When multiLangEnable = true
         * Use system browser language
         */ 
        if(Revship::lib('config')->getItem('site.multiLangEnable'))
        {
            // Use cookie setting if cookie exists 
            if($this->hasCookieLanguage())
            {
                $this->languageCode = $_COOKIE['lang'];
                return true;        
            }
            if(array_key_exists('HTTP_ACCEPT_LANGUAGE',$_SERVER))
            {
                if($_SERVER['HTTP_ACCEPT_LANGUAGE']=='en')
                {
                    $this->languageCode = 'en-us';
                }
                else 
                {   
                    $language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                    preg_match_all("/[\w-]+/",$language,$language);
                    if(isset($language[0][0]))
                    {
                        $area = strtolower($language[0][0]);
                    }
                    if(isset($language[0][1]))
                    {
                        $country = strtolower($language[0][1]);
                    }
                    if(!empty($area))
                    {
                      $this->languageCode = $area;  
                    }
                    else if(!empty($country))
                    {
                      $this->languageCode = $country;  
                    }
                }   
            }
            else
            {
                $this->languageCode = $this->defaultLanguageCode;
            }
        }
        /*
         * If first visit with no cookie.
         * When multiLangEnable = false
         * Use fixed language
         */ 
        else if(Revship::lib('config')->getItem('site.primaryLanguageCode'))
        {
            $this->languageCode = Revship::lib('config')->getItem('site.primaryLanguageCode');
        }
        /*
         * If primaryLangue was not set in config file.
         */
        else 
        {
            $this->languageCode = $this->defaultLanguageCode;
        }
        /*
         * Save to cookie
         */
        $this->setCookieLanguage();
    }
    public function getCurrentLanguageCode()
    {
        return $this->languageCode;
    } 
    protected function hasCookieLanguage()
    {
        if( array_key_exists('lang',$_COOKIE) && $_COOKIE['lang'] != '' )
        {
            return true;
        }
        return false;
    }
    
    /**
     * Set language value to cookie
     * @param unknown_type $lang
     */
    public function setCookieLanguage( $languageCode = null )
    {
        if( ! $languageCode )
        {
            $languageCode = $this->languageCode;
        }
        //setcookie("lang","",time()-3600);
        setcookie("lang",$languageCode,time()+365*24*3600,"/") or die('Fail to set cookie: setCookieLanguage');
        return true;
    }
    
    public function getDomain()
    {
       $domain=$_SERVER['SERVER_NAME'];
       if(strcasecmp($domain,"localhost")===0)
       {
           return $domain;
       }
       if(preg_match("/^(\d+\.){3}\d+$/",$domain,$domain_temp))
       {
           return $domain_temp[0];
       }
       preg_match_all("/\w+\.\w+$/",$domain,$domain);
       return $domain[0][0];
    } 
    
    /**
     * Transform Phrase
     * This function will sprintf the variables array.
     * e.g.
     * transformPhrase('Welcome to %s.','Revship')
     * => 'Welcome to Revship.' 
     * OR 'Bienvenido a Revship.' as Spanish
     * 
     * @param $phrase
     * @param $replaceArray
     * @param $namespace
     */
    public function transformPhrase($phrase, $replaceArray = null, $namespace = 'system')
    {
        // Get language pack phrase from given phrase
        $newPhrase = $this->getTargetPhrase($phrase, $namespace);
        
        // Transform to language pack phrase
        if( $replaceArray == null )
        {
            return $newPhrase;
        }
        else if( ! is_array($replaceArray) )
        {
            $replaceArray = array($replaceArray);
        }
        
        return Revship::lib('transform')->sprintf($newPhrase,$replaceArray);
    }

    /**
     * Get target phrase according to $this->languagePackFile
     * 
     * e.g.
     * getTargetPhrase('Welcome to %s.') 
     * => 'Bienvenido a &s.' as Spanish
     * 
     * @param string $phrase
     */
    protected function getTargetPhrase($phrase, $namespace)
    {
        // No language pack file loaded, return what it was
        if( ! $this->languagePackFile)
        {
            return $phrase;
        }
        //(already checked file_exists)
        
        //Load language array if not loaded before. (Will not twice)
        if( ! array_key_exists($this->languagePackFile,$this->phrases) )
        {
            $this->phrases[$this->languagePackFile] = require($this->languagePackFile);
        }
        if( ! array_key_exists($phrase,$this->phrases[$this->languagePackFile]) )
        {
            return $phrase;
        }   
        return $this->phrases[$this->languagePackFile][$phrase];
    }
    /**
     * Language Code to Language Name
     * 
     * 'zh-cn' => array('Chinese', 'CN', 'PRC', '简体中文'), 
     */
    public function codeToName($code)
    {
        if(isset($this->codeNames[$code]))
        {
            return $this->codeNames[$code];
        }
    }
}
