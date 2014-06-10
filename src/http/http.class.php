<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Http
{
    public static $statusCode = array(
                            200    => 'OK',
                            201    => 'Created',
                            202    => 'Accepted',
                            203    => 'Non-Authoritative Information',
                            204    => 'No Content',
                            205    => 'Reset Content',
                            206    => 'Partial Content',

                            300    => 'Multiple Choices',
                            301    => 'Moved Permanently',
                            302    => 'Found',
                            304    => 'Not Modified',
                            305    => 'Use Proxy',
                            307    => 'Temporary Redirect',

                            400    => 'Bad Request',
                            401    => 'Unauthorized',
                            403    => 'Forbidden',
                            404    => 'Not Found',
                            405    => 'Method Not Allowed',
                            406    => 'Not Acceptable',
                            407    => 'Proxy Authentication Required',
                            408    => 'Request Timeout',
                            409    => 'Conflict',
                            410    => 'Gone',
                            411    => 'Length Required',
                            412    => 'Precondition Failed',
                            413    => 'Request Entity Too Large',
                            414    => 'Request-URI Too Long',
                            415    => 'Unsupported Media Type',
                            416    => 'Requested Range Not Satisfiable',
                            417    => 'Expectation Failed',

                            500    => 'Internal Server Error',
                            501    => 'Not Implemented',
                            502    => 'Bad Gateway',
                            503    => 'Service Unavailable',
                            504    => 'Gateway Timeout',
                            505    => 'HTTP Version Not Supported'
                        );
    
    /**
     * Send a HTTP status code with header()
     * @param int $code
     * @param string $text
     */
    public static function setStatusHeader($code = 200, $text = '')
    {
        if ($code == '' OR ! is_numeric($code))
        {
            throw new Exception('Status codes must be numeric', 500);
        }

        if (isset(self::$statusCode[$code]) && $text == '')
        {
            $text = self::$statusCode[$code];
        }

        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : FALSE;

        if (substr(php_sapi_name(), 0, 3) == 'cgi')
        {
            header("Status: {$code} {$text}", TRUE);
        }
        elseif ($server_protocol == 'HTTP/1.1' OR $server_protocol == 'HTTP/1.0')
        {
            header($server_protocol." {$code} {$text}", TRUE, $code);
        }
        else
        {
            header("HTTP/1.1 {$code} {$text}", TRUE, $code);
        }
    }
    
    /**
     * If has $_POST ?
     * @return boolean
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD']=='POST';
    }
    
    private static function makeSafeUrlForRedirect($url)
    {
        if( preg_match('/#$/', $url) )
        {
            $url = str_replace('#', '', $url);
        }
        if( strpos($url, 'http') ===false )
        {
            $url = str_replace('//', '/', $url);
        }
        return $url;
    }

    public static function getCurrentUrl()
    {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }    

    /**
     * 302 Redirect
     * @param $url
     */
    public static function redirect($url,$exceptArray = null)
    {
        if(is_array($exceptArray))
        {
            foreach($exceptArray as $except)
            {
                $except = str_replace('/','\/',$except);
                preg_match('/'.$except.'/',$url,$matches);
                if(is_array($matches) && count($matches))
                {
                    $url = '/';
                }
            }
        }
        header("HTTP/1.1 302 Moved Temporarily");
        header('Location: ' . self::makeSafeUrlForRedirect($url));
        exit;
    }
    
    /**
     * 301 Redirect
     * @param $url
     */
    public static function redirectPermently($url)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: ' . self::makeSafeUrlForRedirect($url));
        exit;
    }    
    
    /**
     * Redirect to self page
     */
    public static function redirectToSelf()
    {
        $url = $_SERVER['REQUEST_URI'];
        self::redirect($url);
    }
    /**
     * Redirect to referer
     */
    public static function redirectToReferer($default='/',$exceptArray = null)
    {
        $referer = self::getReferer();
        $url = self::getReferer($default);
        if(is_array($exceptArray))
        {
            foreach($exceptArray as $except)
            {
                $except = str_replace('/','\/',$except);
                preg_match('/'.$except.'/',$referer,$matches);
                if(is_array($matches) && count($matches))
                {
                    $url = $default;
                }
            }
        }
        self::getReferer($default);
        header("HTTP/1.1 302 Moved Temporarily");
        header('Location: ' . self::makeSafeUrlForRedirect($url));
        exit;
    }

    /**
     * Get query_string
     * @return string
     */
    public static function getQueryString()
    {
        return $_SERVER['QUERY_STRING'];
    }

    /**
     * Get POST value
     * @param $key                POST's key
     * @param $default            key not exist will return this
     * @return string
     */
    public static function getPOST($key, $default = false)
    {
        preg_match('/(?P<form>\w+)\[(?P<key>\w+)\]$/',$key,$matches);
        if(is_array($matches) && array_key_exists('key',$matches))
        {
            return isset($_POST[$matches['form']][$matches['key']]) ? $_POST[$matches['form']][$matches['key']] : $default;   
        }
        else
        {
            return isset($_POST[$key]) ? $_POST[$key] : $default;
        }
    }

    /**
     * Get GET value
     * @param $key                GET's key
     * @param $default            key not exist will return this
     * @return string
     */
    public static function getGET($key= null, $default = false)
    {
        if($key == null)
        {
            return !empty($_GET) ? $_GET : $default;
        }
        preg_match('/(?P<form>\w+)\[(?P<key>\w+)\]$/',$key,$matches);
        if(is_array($matches) && array_key_exists('key',$matches))
        {
            return isset($_GET[$matches['form']][$matches['key']]) ? $_GET[$matches['form']][$matches['key']] : $default;   
        }
        else
        {
            return isset($_GET[$key]) ? $_GET[$key] : $default;
        }
    }

    /**
     * Get REQUEST value
     * @param $key                REQUEST's key
     * @param $default            key not exist will return this
     * @return string
     */
    public static function getREQUEST($key, $default = false)
    {
        preg_match('/(?P<form>\w+)\[(?P<key>\w+)\]$/',$key,$matches);
        if(is_array($matches) && array_key_exists('key',$matches))
        {
            return isset($_REQUEST[$matches['form']][$matches['key']]) ? $_REQUEST[$matches['form']][$matches['key']] : $default;   
        }
        else
        {
            return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
        }
    }

    /**
     * Get the referer url
     * @param $default    Return value if there's no referer
     * @return string
     */
    public static function getReferer($default = false)
    {
        if(isset($_SERVER['HTTP_REFERER']))
            return $_SERVER['HTTP_REFERER'];
        else
            return $default;
    }
    
    /**
     * Referer: http://aaa.com/bcd
     * return bcd
     */
    public static function getPureReferer()
    {
        $string = (Revship::lib('config')->getItem('site.domainProtocol') ? Revship::lib('config')->getItem('site.domainProtocol') : 'http').'://'.$_SERVER['HTTP_HOST'].'/';
        $len = strlen($string);
        return substr(self::getReferer(), $len);
    }
    /**
     * Current domain (HTTP_HOST)
     */
    public static function getHost()
    {
        return $_SERVER['HTTP_HOST'];
    }
    
    public static function getProtocol()
    {
        if(strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS')!==false || $_SERVER['SERVER_PORT'] == '443' )
        {
            return 'https';
        }
        else
        {
            return 'http';
        }
    }
    
    /**
     * Get visitor's IP address
     */
    public static function getIp($useInt=true)
    {
        if(getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif(getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif(getenv('REMOTE_ADDR'))
        {
            $ip = getenv('REMOTE_ADDR');
        }
        else
        {
           $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }
        if( $useInt )
        {
            $ip = self::ip2long($ip);
        }
        return $ip;
    }
    public static function getUserAgent($default = '')
    {
        if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'])
        {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        else
        {
            return $default;
        }
    }
    /**
     * Render in JSON data
     * (Content-Type and json_encode included)
     * Requirement for json_encode:
     * PHP >= 5.2.0 OR PECL json >= 1.2.0
     */
    public static function renderJSON($array)
    {
        header('Content-Type:text/javascript; charset=UTF-8');
        echo json_encode($array);
        exit;
    }
    
    /**
     * Render a JS only page
     * 
     * @param unknown_type $jsString
     */
    public static function renderJsPage($jsString, $loadJquery = false)
    {
        header('Content-Type:text/html; charset=UTF-8');
        echo 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>';
        if($loadJquery)
        {
            echo Revship::lib('html')->jsFile('/public/js/jquery.min.js');
        }
        echo '<script type="text/javascript">
'.$jsString.'
</script>
</head>
<body>
</body>
</html>';
        exit;
    }
    
    /**
     * ip2long
     * Original function will produce negative in Windows
     * @param string $ip
     * @return int
     */
    public static function ip2long($ip) {
        return sprintf('%u' , ip2long($ip));
    }
    
    /**
     * long2ip
     * @param int $long
     * @return string
     */
    public static function long2ip($long) {
        return long2ip($long);
    }
    /**
     * Get Root Domain
     * abc.def.com  =>  def.com
     * @copyright http://forums.devshed.com/php-development-5/find-root-domain-in-a-url-551863.html
     */
    public static function getRootDomain( $host = null )
    {
        if(!$host)
        {
            $host = self::getHost();
        }
        $host = array('domain' => $host);
        if ( ( $total_parts = substr_count ( $host['domain'], '.' ) ) <= 1 )
        {
            return $host['domain'];
        }
        $parts_array = explode ( '.', $host['domain'] );
        $last_part = $parts_array[$total_parts];
        $test_part = $parts_array[--$total_parts] . '.' . $last_part;
        $top_names = 'ac.cn,ac.jp,ac.uk,ad.jp,adm.br,adv.br,agr.br,ah.cn,am.br,arq.br,art.br,asn.au,ato.br,av.tr,bel.tr,bio.br,biz.tr,bj.cn,bmd.br,cim.br,cng.br,cnt.br,co.at,co.jp,co.uk,com.au,com.br,com.cn,com.eg,com.hk,com.mx,com.ru,com.tr,com.tw,conf.au,cq.cn,csiro.au,dr.tr,ecn.br,edu.au,edu.br,edu.tr,emu.id.au,eng.br,esp.br,etc.br,eti.br,eun.eg,far.br,fj.cn,fm.br,fnd.br,fot.br,fst.br,g12.br,gb.com,gb.net,gd.cn,gen.tr,ggf.br,gob.mx,gov.au,gov.br,gov.cn,gov.hk,gov.tr,gr.jp,gs.cn,gx.cn,gz.cn,ha.cn,hb.cn,he.cn,hi.cn,hk.cn,hl.cn,hn.cn,id.au,idv.tw,imb.br,ind.br,inf.br,info.au,info.tr,jl.cn,jor.br,js.cn,jx.cn,k12.tr,lel.br,ln.cn,ltd.uk,mat.br,me.uk,med.br,mil.br,mil.tr,mo.cn,mus.br,name.tr,ne.jp,net.au,net.br,net.cn,net.eg,net.hk,net.lu,net.mx,net.ru,net.tr,net.tw,net.uk,nm.cn,no.com,nom.br,not.br,ntr.br,nx.cn,odo.br,oop.br,or.at,or.jp,org.au,org.br,org.cn,org.hk,org.lu,org.ru,org.tr,org.tw,org.uk,plc.uk,pol.tr,pp.ru,ppg.br,pro.br,psc.br,psi.br,qh.cn,qsl.br,rec.br,sc.cn,sd.cn,se.com,se.net,sh.cn,slg.br,sn.cn,srv.br,sx.cn,tel.tr,tj.cn,tmp.br,trd.br,tur.br,tv.br,tw.cn,uk.com,uk.net,vet.br,wattle.id.au,web.tr,xj.cn,xz.cn,yn.cn,zj.cn,zlg.br,co.nr,co.nz,com.fr,';
        if ( strpos ( $top_names, $test_part . ',' ) )
        {
            $last_part = $parts_array[--$total_parts] . '.' . $test_part;
            if ( strpos ( $top_names, $last_part . ',' ) )
            {
                $host['toplevelname']   = $last_part;
                $last_part              = $parts_array[--$total_parts] . '.' . $last_part;
                $host['topleveldomain'] = $last_part;
                $host['subdomain']      = str_ireplace ( '.' . $last_part, '', $host['domain'] );
            }
            else
            {
                $host['topleveldomain'] = $last_part;
                $host['subdomain']      = str_ireplace ( '.' . $last_part, '', $host['domain'] );
                $host['toplevelname']   = $test_part;
            }
        }
        else
        {
            $host['topleveldomain'] = $test_part;
            $host['subdomain']      = str_ireplace ( '.' . $test_part, '', $host['domain'] );
            $host['toplevelname']   = $last_part;
        }
        return $host['topleveldomain'];
    }
}
