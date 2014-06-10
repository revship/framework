<?php

class Revship_Curl
{
    public $mClassName     = null;
    public $mDomain        = null;
    public $mLoginUrl      = null;
    public $mUrl           = null;
    public $mCookFile      = null;
    public $mIsOutputHeader= false;//是否输出head
    public $mIsNoBody      = false; //是否不输出body
    public $mIsLocation    = false;//是否允许跳转
    public $mVerbose       = false; //详细报告
    public $mReturntransfer= 1;    //
    public $mTimeout       = 8;    //设置curl允许执行的最长秒数
    public $mReturnHtml    = null; //返回的html内容
    public $mInfo          = null; //curl_getinfo返回值
    public $mProxy         = null; //代理ip
    public $mMethod     = "post";
    private $mSslCert   = null; //ssl 私钥证书string
    private $mSslCertPasswd = null;//ssl 私钥证书密码
    private $mSslKey    = null; //ssl 私钥证书string
    private $mSslKeyPasswd = null;//ssl 私钥证书密码
    private $mSslCaFile = null;
    private $mSslCheck  = false;
    private $logPath    = "/tmp/";

    /** {{{http 头
     *$this_header = array(
     *  "MIME-Version: 1.0",
     *  "Content-type: text/html; charset=iso-8859-1",
     *  "Content-transfer-encoding: text"
     *  );
      */
    public $mHttpHeader    = null;
    //}}}
    public $mReferer       = null;

    public $hZZTestLogin   = null;//登陆后的用户记录

    /** {{{ 构造函数 __construct()
      */
    public function __construct(){
        $this->mClassName = __CLASS__;
    }//}}}
    /** {{{ curl方式登陆占座 login($url,$request)
      * 根据用户提交的参数来判断是post登陆还是get登陆url这个网站
      * @param  string   登陆的url
      * @param  array   $request=null时不需要post数据
      * @return string
      * @see ZZTest::get
      * @see ZZTest::post
      */
    public function login($url,$request=null){
        $this->mCookFile = tempnam('/tmp','cookie');
        if(file_exists($this->mCookFile)){//文件存在
            $fp = @fopen($this->mCookFile,"r+");
            $ok = @ftruncate($fp,'0');
            @fclose($fp);
            if($ok == false){
                $this->setMsg('无法生成cookie文件');
                return false;
            }
        }
        $ch = curl_init();
        if($this->mMethod=="post"){//支持get方式登录
            $this->mReturnHtml = $this->post($url,$request);
        }else{
            if(false===strpos($url,"?")){
                $url .= "?".$this->dataEncode($request);
            }else{
                $url .= "&".$this->dataEncode($request);
            }
            $this->mReturnHtml = $this->get($url);
        }
        return $this->mReturnHtml;
    }//}}}
    /** {{{ 向某个url 请求数据 post($url,$post=null)
      * @param  string   要请求的url 地址
      * @param  array    要post的数据
      * @param  boolean  是否要上传文件,true的时候，不进行编码
      * @return string
      */
    public function post($url,$post= null ,$updatefile = false){
        if(false == $updatefile){
            $post = $this->dataEncode($post);
        }
        if($post == false ){
            $this->setMsg('post 请求数据错误');
            return false;
        }
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, $url);   
        curl_setopt($ch, CURLOPT_HEADER, $this->mIsOutputHeader);   
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->mIsLocation);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->mVerbose);//报告每一个细节
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->mCookFile); //设定返回的数据是否自动显示
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->mTimeout);   
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->mCookFile);   
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; (R1 1.5))');
        if(is_array($this->mHttpHeader)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->mHttpHeader);
        //{{{ ssl
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$this->mSslCheck);
        /*
        if(!empty($this->mSslCert)) curl_setopt($ch,CURLOPT_SSLCERT,$this->mSslCert);
        if(!empty($this->mSslCertPasswd)) curl_setopt($ch,CURLOPT_SSLCERTPASSWD,$this->mSslCertPasswd);
        if(!empty($this->mSslKey)) curl_setopt($ch,CURLOPT_SSLKEY,$this->mSslKey);
        if(!empty($this->mSslKeyPasswd)) curl_setopt($ch,CURLOPT_SSLKEYPASSWD,$this->mSslKeyPasswd);
        if(!empty($this->mSslCaFile)) curl_setopt($ch,CURLOPT_CAINFO,$this->mSslCaFile);
        */
        //}}}
        $msg = "请求url:".$url;
        $html = curl_exec($ch);
        if ($ok = curl_errno($ch)) {
            $this->setMsg(curl_error($ch));
            $msg .= "失败";
            $this->log($msg);
            return false;
        }
        curl_close($ch);    
        $msg .= "成功";
        $this->log($msg);
        return $html;
    }//}}}
    /** {{{ 向某个url GET请求数据 get($url)
     * @param  string   要请求的url 地址
     * @return string   请求这个url返回的html代码
     */
    public function get($url){
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, $url);   
        curl_setopt($ch, CURLOPT_HEADER, $this->mIsOutputHeader);   
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->mCookFile); //设定返回的数据是否自动显示
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->mReturntransfer);   
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->mTimeout);   
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->mCookFile);   
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->mIsLocation);
        curl_setopt($ch, CURLOPT_NOBODY, $this->mIsNoBody);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->mVerbose);//报告每一个细节
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; (R1 1.5))');
        //{{{ ssl
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$this->mSslCheck);
        /*
        if(!empty($this->mSslCert)) curl_setopt($ch,CURLOPT_SSLCERT,$this->mSslCert);
        if(!empty($this->mSslCertPasswd)) curl_setopt($ch,CURLOPT_SSLCERTPASSWD,$this->mSslCertPasswd);
        if(!empty($this->mSslKey)) curl_setopt($ch,CURLOPT_SSLKEY,$this->mSslKey);
        if(!empty($this->mSslKeyPasswd)) curl_setopt($ch,CURLOPT_SSLKEYPASSWD,$this->mSslKeyPasswd);
        if(!empty($this->mSslCaFile)) curl_setopt($ch,CURLOPT_CAINFO,$this->mSslCaFile);
        */
        //}}}
        if($this->mProxy){
            curl_setopt($ch, CURLOPT_PROXY,$this->mProxy);
            curl_setopt($ch, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
        }
        if(null != $this->mReferer) curl_setopt($ch, CURLOPT_REFERER, $this->mReferer);
        if(__CLASS__ != $this->mClassName) curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this->mClassName,'readHeader'));
        if(is_array($this->mHttpHeader)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->mHttpHeader);
        $msg = "请求url:".$url;
        $html = curl_exec($ch);   
        if ($ok = curl_errno($ch)) {
            $this->setMsg(curl_error($ch));
            $msg .= "失败";
            $this->log($msg);
            return false;
        }
        $this->mInfo = curl_getinfo($ch);
        $msg .= "成功";
        $this->log($msg);
        curl_close($ch);    
        return $html;
    }//}}}
    /** {{{ 下载文件 download($url,$filename)
     * @param  string  要下载的url
     * @param  string  存储在本地文件名 文件名为空标示使用下载文件的文件名
     */
    public function download($url,$filename = ''){
        if ( empty($url) ){
            return false;
        }
        if( empty($filename) ){
            $url_info = parse_url($url);
            $filename = "/tmp/" . basename($url_info['path']);
        }
        $this->log($url);
        $fp = @fopen($filename, "w");
        if($fp){
            $this->log($filename);
            $ch = curl_init();   
            curl_setopt($ch, CURLOPT_URL, $url);   
            curl_setopt($ch, CURLOPT_HEADER, $this->mIsOutputHeader);   
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->mCookFile); //设定返回的数据是否自动显示
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->mReturntransfer);   
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->mTimeout);   
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->mCookFile);   
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->mIsLocation);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; (R1 1.5))');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_exec($ch);
            if ($ok = curl_errno($ch)) {
                $msg = curl_error($ch);
                $this->log($msg);
                return false;
            }
            curl_close($ch);

            fclose($fp);
            return $filename;
        }
        return false;
    }//}}} 
    /** {{{ 转换成post字符串 dataEncode($data,$keyprefix,$keyprefix);
     */
    function dataEncode($data, $keyprefix = "", $keypostfix = "") {
        if(is_array($data)) return http_build_query($data);
        else{
            return $data;
        }
    }//}}}
    /** {{{ 设定这次的登陆域名 setDomain($domain)
     * <code>
     * </code>
     *
     */
    function setDomain($domain){
        $this->mDomain = $domain;
    }//}}}
    /** {{{ 设定这次的登陆URL   setURL($url)
     * <code>
     * </code>
     *
     */
    function setURL($url){
        if(empty($this->mDomain)){
            return false;
        }
        $this->mUrl = $this->mDomain."/".$url;
    }//}}}
    /** {{{ 读取http Code信息 getHttpCode($url, $timeout)
     */
    public function getHttpCode($url,$timeout = 30){
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, $url);   
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);   
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $html = curl_exec($ch);   
        if ($ok = curl_errno($ch)) {
            return false;
        }
        $info = curl_getinfo($ch);
        curl_close($ch);    
        return (int)$info['http_code'];
    }//}}}
    /** {{{ 分析头信息 parseHeader($header)
      * @param  string
      * @return array|false
     */
    protected function parseHeader($header){
        $pos = strpos($header,"\r\n\r\n");
        $header = substr($header,0,$pos);
        $h = explode("\r\n",$header);
        if(is_array($h)){
            $r = array();
            foreach($h as $k=>$v){
                $tmp = explode(":",$v,2);
                if(2 == count($tmp)){
                    if('Date' == $tmp[0]) $r['time'] = strtotime($tmp[1]);
                    $r[$tmp[0]] = trim($tmp[1]);
                }
            }
            return $r;
        }
        return false;
    }//}}}
    /** {{{ 设定ssl私钥文件地址 setSslKey($filename,$passwd = '')
     *
     */
    public function setSslKey($filename,$passwd){
        if(file_exists($filename)){
            $this->mSslKey = $filename;
        }
        $this->mSslKeyPasswd = $passwd;
    }//}}}
    /** {{{ 设定ssl cert setSslCert($filename,$passwd = '')
     *
     */
    public function setSslCert($filename,$passwd){
        if(file_exists($filename)){
            $this->mSslCert = $filename;
        }
        $this->mSslCertPasswd = $passwd;
    }//}}}
    /** {{{ 设定ssl cert setSslCaFile($filename)
     *
     */
    public function setSslCaFile($filename){
        if(file_exists($filename)){
            $this->mSslCaFile= $filename;
        }
    }//}}}
    /** {{{ 记录log log($msg)
      * @param  string  错误消息
      * @return void
     */
    function log($msg,$logfile = null){
        return;
    }//}}}
    public function setMsg($msg){
        return false;
    }
}