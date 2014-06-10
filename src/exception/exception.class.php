<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Exception extends Exception
{
    protected $_config;
    protected static $obLevel;
    protected static $noThemeCode= array();

    protected $levels = array(
            E_ERROR                =>    'Error',
            E_WARNING            =>    'Warning',
            E_PARSE                =>    'Parsing Error',
            E_NOTICE            =>    'Notice',
            E_CORE_ERROR        =>    'Core Error',
            E_CORE_WARNING        =>    'Core Warning',
            E_COMPILE_ERROR        =>    'Compile Error',
            E_COMPILE_WARNING    =>    'Compile Warning',
            E_USER_ERROR        =>    'User Error',
            E_USER_WARNING        =>    'User Warning',
            E_USER_NOTICE        =>    'User Notice',
            E_STRICT            =>    'Runtime Notice'
            );

    public function __construct ($status=500, $message, $code = E_USER_ERROR)
    {
        $message = Revship::l($message);
        self::$obLevel = ob_get_level();
        if( ( $status >=401 && $status<406 ) || ! Revship::isDebug())
        {
            $this->showError($status,$message);
        }

        parent::__construct($message,$code);
        Revship::log($this->levels[$code].' - ' . $status.' - '.$message,'EXCEPTION');
        Revship::log($status.' - '.$message.' - '.print_r(debug_backtrace(),true),'EXCEPTION-DETAIL');   
        //Revship::end();
    }
    public static function showError ($status=500, $message, $template = 'error')
    {
        Revship_Http::setStatusHeader($status);
        $message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

        //if(!in_array($status,self::$noThemeCode))
            self::showErrorWithTheme($status, $message, $template);
        //else
        //    Revship::end($message);
    }
    protected function loadConfig()
    {
        $this->_config = Revship::lib('config');
    }
    protected static function showErrorWithTheme($status, $message, $template )
    {
        $message = Revship::l($message);
        if (ob_get_level() > self::$obLevel + 1)
        {
            ob_end_flush();
        }

        $statusText = Revship_Http::$statusCode[$status];

        ob_start();
        // theme/default/error/
        $templatePath= THEME_PATH . Revship::lib('config')->getItem('theme.dirname') . DS . 'error' . DS .$template . '.php';

        //@todo Smarty
        if(file_exists($templatePath))
            include($templatePath);
        else
            echo $message;

        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
        exit();
    }

    private static function show_php_error($severity, $message, $filepath, $line)
    {    
        @ob_start();
        require(RS_PATH.'exception/template/phpError.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }



    public static function exception_handler($nErrNo, $sErrMsg, $sFileName, $nLinenum, $aVars)
    {
        @Revship::log($nErrNo.' - ' . $sErrMsg.' - '.$sFileName. ' on line '.$nLinenum,'EXCEPTION');
        @Revship::log($nErrNo.' - ' . $sErrMsg.' - '.$sFileName. ' on line '.$nLinenum,' - '.print_r(debug_backtrace(),true),'EXCEPTION-DETAIL');
        if( ! Revship::lib('config')->getItem('site.debug'))
        {
            return;
        }
        $aTypes = array(
                1   =>  "Error",
                2   =>  "Warning",
                4   =>  "Parsing Error",
                8   =>  "Notice",
                16  =>  "Core Error",
                32  =>  "Core Warning",
                64  =>  "Compile Error",
                128 =>  "Compile Warning",
                256 =>  "User Error",
                512 =>  "User Warning",
                1024=>  "User Notice",
                2048=>  "PHP 5"
                );

        $aColors = array(
                1   =>  "#ffd1d1",
                2   =>  "#d9eefe",
                4   =>  "#ffd1d1",
                8   =>  "#d7ffd7",
                16  =>  "#ffd1d1",
                32  =>  "#d9eefe",
                64  =>  "#ffd1d1",
                128 =>  "#d9eefe",
                256 =>  "#ffd1d1",
                512 =>  "#d9eefe",
                1024=>  "#ffd1d1",
                2048=>  "#ffd1d1"
                );

        if (substr(PHP_VERSION, 0, 1) < 5)
        {
            $iStart = 1;
        }
        else
        {
            $iStart = 0;
        }        

        $bNoHtml = false;
        if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') || (PHP_SAPI == 'cli'))
        {
            $bNoHtml = true;    
        }

        $sErrMsg = str_replace(SITE_PATH, '', $sErrMsg);

        //Filter DB
        if(strpos($sErrMsg,'mysql_connect()')!==false ||
                strpos($sErrMsg,'mysql_select_db()')!==false ||
                strpos($sErrMsg,'mysqli_connect()')!==false
          )
        {
            return;
        }    



        if ($bNoHtml)
        {
            $sErr = "\n{$aTypes[$nErrNo]}: {$sErrMsg}\n";
        }
        else 
        {
            $sErr = '<br />
                <table border="0" cellspacing="0" cellpadding="2" style="font-family:Verdana;font-size:12px; border: 1px solid #ccc; border-bottom:0; background:#fff;">
                <tr>
                <td colspan="10" align="left" valig="top" style="padding:5px; border-bottom:1px #ccc solid; background-color: ' . $aColors[$nErrNo] . '"><b>' . $aTypes[$nErrNo] . ':&nbsp;' . $sErrMsg . ' - ' . str_replace(SITE_PATH, '', $sFileName) . ' (' . $nLinenum . ')</b></td></tr>';      
        }        

        $aFiles = debug_backtrace();

        for ($i=$iStart, $n=sizeof($aFiles); $i<$n; ++$i)
        {
            $sArgs = '';
            if (isset($aFiles[$i]['args']))
            {
                $aArgs = array();
                $aArgs = array_merge($aFiles[$i]['args'], array());
                if ($aArgs and is_array($aArgs))
                {
                    foreach ($aArgs as $k=>$v)
                    {
                        if (is_numeric($v))
                        {
                            $aArgs[$k] = '<span style="color:#6d009b">'.$v.'</span>';
                        }
                        elseif(is_bool($v))
                        {
                            $aArgs[$k] = '<span style="color:#222288;">'.($v ? 'TRUE' : 'FALSE').'</span>';
                        }
                        elseif(is_null($v))
                        {
                            $aArgs[$k] = '<span style="color:#222288;">NULL</span>';
                        }
                        elseif(is_array($v))
                        {
                            $aArgs[$k] = 'Array('.count($v).')';
                                    }
                                    elseif (is_string($v) && ! (('"' == substr($v,0,1)) && ('"' == substr($v,-1))))
                                    {
                                    $aArgs[$k] = '<span style="color:#1919b4">"'.$v.'"</span>';
                                    }
                                    elseif(is_object($v))
                                    {
                                    unset($aArgs[$k]);
                                    $aArgs[$k] = '{' . ucfirst(get_class($v)) . '}';
                                    }
                                    }
                                    }
                                    $sArgs = implode(', ', $aArgs);
                                    }

                                    $sFuncName = (isset($aFiles[$i]['class'])?$aFiles[$i]['class']:'').
                                    (isset($aFiles[$i]['type'])?$aFiles[$i]['type']:'').
                                    $aFiles[$i]['function'].'('.$sArgs.')';
                                    if ($iStart == $i)
                                    {
                                        $sFuncName = '<b>' . $sFuncName . '</b>';
                                    }
                                    if(array_key_exists('file',$aFiles[$i]))
                                        $sFile = str_replace(SITE_PATH, '', $aFiles[$i]['file']);
/*
                                    if ($bNoHtml)
                                    {
                                        $sErr .= "{$i}\t{$sFile}\t" . (isset($aFiles[$i]['line']) ? $aFiles[$i]['line'] : '') . "\t" . strip_tags(str_replace(SITE_PATH, '', $sFuncName)) . "\n";    
                                    }
                                    else 
                                    {
                                        */
                                        $sErr .= '<tr><td style="background:#ddd" align="right">'.$i.'&nbsp;</td>';
                                        if(isset($sFile))
                                        {
                                            $sErr .= '<td style="border-bottom:1px #ccc solid; padding:5px;">' . $sFile . '&nbsp;:&nbsp;<b>'.(isset($aFiles[$i]['line']) ? $aFiles[$i]['line'] : '').'</b>&nbsp;&nbsp; </td>'.
                                                '<td style="border-bottom:1px #ccc solid; padding:5px; border-left:1px #ccc solid;">' . str_replace(SITE_PATH, '', $sFuncName) . '</td></tr>';
                                        }
                                        else
                                        {
                                            $sErr .= '<td colspan=2 style="border-bottom:1px #ccc solid; padding:5px; border-left:1px #ccc solid;">' . str_replace(SITE_PATH, '', $sFuncName) . '</td></tr>';
                                        }

                                    //}
        }          
        if (!$bNoHtml)
        {
            $sErr .= '</table>';
        }
        echo $sErr;        

    }    

    /*
       public static function exception_handler2($severity, $message, $filepath, $line)
       {
    // We don't bother with "strict" notices since they tend to fill up
    // the log file with excess information that isn't normally very helpful.
    // For example, if you are running PHP 5 and you use version 4 style
    // class functions (without prefixes like "public", "private", etc.)
    // you'll get notices telling you that these have been deprecated.
    if ($severity == E_STRICT)
    {
    return;
    }

    // Should we display the error? We'll get the current error_reporting
    // level and add its bits with the severity bits to find out.
    if (($severity & error_reporting()) == $severity)
    {
    self::show_php_error($severity, $message, $filepath, $line);
    }

    // Should we log the error?  No?  We're done...
    if (config_item('log_threshold') == 0)
    {
    return;
    }

    $_error->log_exception($severity, $message, $filepath, $line);

    }
     */
}
