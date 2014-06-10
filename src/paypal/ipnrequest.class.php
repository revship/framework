<?php 
/**
 * ipn instant payment notification verify 
 * @author hxl
 *
 */
require_once dirname(__FILE__) . '/base.class.php';
class Revship_Paypal_Ipnrequest extends PayPalRequest{
    protected function handler(){
        if ($this->checked === -1){
            Revship::log("\n\n====REQUEST HANDLER CHECKED=-1====\n",'paypal-fail');
            throw new Revship_Exception(403,"IPN Response can not checked!");
        }
    }
    protected function createRequest(){
        $req = 'cmd=_notify-validate';
        foreach ($this->data as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        return $req;
    }
    
    protected function responseHandler($res){
        if (strcmp ($res, "VERIFIED") == 0) {
            $this->checked = TRUE;
        } else if (strcmp ($res, "INVALID") == 0) {
            $this->checked = FALSE;
        }
        return $res;
    }
}
