<?php
/**
 * the base ipn and pdt verify of paypal
 * Instant Payment Notification or Payment Data Transfer 
 * 
 * @author hxl dwoon@yahoo.cn
 *
 */
class PayPalRequest{
    protected static $PayPalHost = "ssl://www.paypal.com";
    protected static $HeadUrl = "Host: www.paypal.com";
    protected static $PayPalPort = 443;
    function __construct(){
        if(Revship::lib('config')->getItem('paypal.is_test'))
        {
            self::$PayPalHost = "ssl://www.sandbox.paypal.com";
            self::$HeadUrl = "Host: www.sandbox.paypal.com";
        }
    }
    /**
     *  @var $request from paypal
     */
    protected $request = NULL;
    protected $response = NULL;
    protected $error = "";
    protected $errno = 0;
    //is checked
    protected $checked = -1;
    protected $data = array();
    /**
     * @return bool
     */
    public function verify(){
        if($this->response === NULL){
            try{
                //initial checked 
                $this->checked = -1;
                //Log::debug("Request-------------".get_class($this)."-----------------");
                //create the request from the post
                $this->request = $this->createRequest();
                //conect
                $this->connect();
                //hadler the respond
                $this->handler();
            } catch (Exception $e){
                throw $e;
            }
        }
        return $this->checked;
    }

    public function setPostData($data){
        $this->data = $data;
    }
    //get the paramater of post
    public function getParamaters(){
        return $this->data;
    }     
    /**
     * combination Request    
     * @return string
     */
     protected function createRequest(){
        return "";
     }
     /**
      *
      * @param int $reqLength
      * @return string
      */
      private function getHeader($reqLength){
        $header = "";
        $header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= self::$HeadUrl . "\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . $reqLength . "\r\n\r\n";
        return $header;
      }
      /**
       * @throws Exception
       */
       private function connect(){
            $fp = fsockopen(self::$PayPalHost, self::$PayPalPort, $this->errno, $this->error, 30);
            if(! $fp){
                throw new Exception("Connect to PayPal Error! ".$this->error, $this->errno);
            } else {
                $header = $this->getHeader(strlen($this->request));
                $socketContent = $header.$this->request;
                //Log::debug("PayRequest: ".$socketContent);
                fputs($fp, $socketContent);
                $res = "";
                while(!feof($fp)){
                    $res .= $this->responseHandler(fgets($fp, 1024));
                }
                $this->response = $res;
                //Log::debug("PayResponse: ".$this->response);
                fclose($fp);
            }
        }
      protected function responseHandler($res){
        return $res;
      }
      protected function handler(){
      }
}
