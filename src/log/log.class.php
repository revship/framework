<?php
class Revship_Log
{
    public function log($msg, $type='DEBUG')
    {
        if(is_array($msg))
        {
            $msg = var_export($msg,true);
        }
        if($type == 'DEBUG')
        {
            if(Revship::lib('config')->getItem('site.debug')!=true)
            {
                return true;
            }
        }
        /*else if($type == 'TRACE')
        {
            echo date('Y-m-d H:i:s').' - '.$msg."<br />\n";
        }*/
        error_log(date('Y-m-d H:i:s').' - '.$msg. "\n", 3, LOG_PATH . "{$type}-" . date('Ymd') . '-' . substr( md5( Revship::lib('config')->getItem('site.licenseNumber') ), 0, 8) );
    }
}