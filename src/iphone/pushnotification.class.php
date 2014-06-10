<?php
/**
 * iPhone Push Notification Sender
 * 
 * @see http://www.cocoachina.com/bbs/read.php?tid-30410.html
 */
class Revship_Iphone_Pushnotification
{
    public $certPemPath = "./ck.pem";
    
    public function send($deviceToken, $message=null, $badge = 1, $sound = 'received5.caf')
    {
        $body = array("aps" => array("alert" => $message, "badge" => $badge, "sound" => $sound));
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $this->certPemPath);
        $fp = stream_socket_client("ssl://gateway.sandbox.push.apple.com:2195", $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            Revship::log(__METHOD__.'-'."Failed to connect {$err}, {$errstr}",'IPHONE_PUSH');
            return false;
        }
        //print "Connection OK\n";
        $payload = json_encode($body);
        $msg = chr(0) . pack("n",32) . pack("H*", $deviceToken) . pack("n",strlen($payload)) . $payload;
        //print "sending message :" . $payload . "\n";
        fwrite($fp, $msg);
        fclose($fp);
        return true;
    }
}