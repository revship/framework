<?php

/*
rewrite  ^/tel/(.*)_(\d+)(_(.*))?.png /utils/tel_image.php?key=$1&t=$2&city=$4 break;
rewrite  ^/tel/(.*).png /utils/tel_image.php?key=$1&t=0 break;
rewrite '^(.*._thumb\d+).(ico|gif|jpg|jpeg|png)$' /utils/image_resize.php?img=$1.$2 last;
*/
class Revship_Captcha_Text2image
{
    protected $fontSize = 18;
    // Image Max Width 
    protected $width = 255;
    protected $colorR = 25;
    protected $colorG =  123;
    protected $colorB = 48;
    protected function hex2bin($data){
        $len = strlen($data);
        return pack("H" . $len, $data);
    }
    
    protected function keyED($txt, $encrypt_key){
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i ++) {
            if ($ctr == strlen($encrypt_key))
                $ctr = 0;
            $tmp .= substr($txt, $i, 1) ^ substr($encrypt_key, $ctr, 1);
            $ctr ++;
        }
        return $tmp;
    }
    protected function tel_encrypt($txt,$telKey = "Revship#(@Default#!Key")//密钥 
    {  
        $rand = date('l jS h');
        $encryptKey = md5( $rand );  
        $ctr=0;  
        $tmp = "";  
        for ($i=0;$i<strlen($txt);$i++)  
        {  
            if ($ctr==strlen($encryptKey)) $ctr=0;  
            $tmp.= substr($encryptKey, $ctr,1) .  
            (substr($txt,$i,1) ^ substr($encryptKey,$ctr,1));  
            $ctr++;  
        }  
        return bin2hex(self::keyED($tmp,$telKey));  
    }
    //电话号码解密函数
    protected function tel_decrypt($txt, $tel_key = "Revship#(@Default#!Key")//密钥
    {
        $txt = $this->keyED($this->hex2bin($txt), $tel_key);
        $tmp = "";
        for ($i = 0; $i < strlen($txt); $i ++) {
            $md5 = substr($txt, $i, 1);
            $i ++;
            $tmp .= (substr($txt, $i, 1) ^ $md5);
        }
        return $tmp;
    }
    
    protected function tel_wp_image($telstr){
        if (strpos($telstr, chr(0)) == true) {
            list ( $type, $str ) = explode(chr(0), $telstr);
            if ($type == 1) {
                $width = strlen($str) * 11 + 11;
                $im = imagecreate($width, 25);
                $black = ImageColorAllocate($im, 0, 0, 0);
                $white = ImageColorAllocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $white);
                imagettftext($im, 16, 0, 1, 17, $black, FONT_FILE, $str);
                header("Content-type: image/png");
                imagepng($im);
                imagedestroy($im);
                exit();
            }
        }
    
    }
    
    protected function random_color_value($value){
        return $value + (255 - $value) * 0.6;
    }
    
    protected function random_light_color($color){
        return array (
                $this->random_color_value($color [0]), 
                $this->random_color_value($color [1]), 
                $this->random_color_value($color [2]) 
        );
    }
    
    protected function wave_image($im, $config){
        $scale = $config ['scale'];
        $Xperiod = $config ['Xperiod'];
        $Xamplitude = $config ['Xamplitude'];
        $width = imagesx($im);
        $height = imagesy($im);
        
        $xp = $scale * $Xperiod * rand(1, 2);
        $k = rand(0, 100);
        for ($i = 5; $i < ($width * $scale); $i ++) {
            imagecopy($im, $im, $i - 1, sin($k + $i / $xp) * ($scale * $Xamplitude), $i, 0, 1, $height * $scale);
        }
    }
    
    protected function draw_matric($im, $rgb){
        $x = rand(0, 5);
        $y = rand(0, 5);
        $w = imagesx($im);
        $h = imagesy($im);
        for ($xx = $x; $xx < $w; $xx += 10) {
            $color = ImageColorAllocate($im, $this->random_color_value($rgb [0]), $this->random_color_value($rgb [1]), $this->random_color_value($rgb [1]));
            imageline($im, $xx, 0, $xx, $h, $color);
        }
        for ($yy = $y; $yy < $h; $yy += 10) {
            $color = ImageColorAllocate($im, $this->random_color_value($rgb [0]), $this->random_color_value($rgb [1]), $this->random_color_value($rgb [1]));
            imageline($im, $x, $yy, $w, $yy, $color);
        }
    }
    /**
     * 随机调整字符间距，增加OCR难度
     */
    protected function random_imagettftext($im, $size, $angle, $left, $top, $rgb, $fontfile, $text, $gap = 4){
        //draw_matric($im, $rgb);
        $color = ImageColorAllocate($im, $rgb [0], $rgb [1], $rgb [2]);
        $x = $left + 1;
        $last = '';
        $factor = 2;
        $len = strlen($text);
        $minus_flag = 0;
        for ($i = 0; $i < strlen($text); $i ++) {
            $letter = substr($text, $i, 1);
            $shift = 0;
            if(isset($nextX) && $nextX)
            {
                $x += $nextX;    
            }
            if ($letter == '1') {
                $size1 = $size + 2;
                $shift = 1;
                $nextX = 2;
                $x -=2;
            } else {
                $shift = rand(-1, 1);
                $size1 = $size + $shift;
                $nextX = 0;
            }
            $box = imagettftext($im, $size1, 0, $x, $top + $shift, $color, $fontfile, $letter);
            $x += $box [2] - $box [0];
            $last = $letter;
        }
        
        $h = imagesy($im);
        $im_new = imagecreate($x + 5, $h);
        imagecopy($im_new, $im, 0, 0, 0, 0, $x + 5, $h);
        return $im_new;
    }
    
    protected function random_imagettftext2($im, $size, $angle, $left, $top, $rgb, $fontfile, $text, $gap = 6){
        $x = $this->random_imagettftext($im, $size, $angle, $left, $top, $rgb, $fontfile, $text, $gap);
        $color = ImageColorAllocate($im, 128, 128, 128);
        return $x;
    }
    
    protected function generateImage($telstr){
        $font_file = dirname(__FILE__) . '/fonts/gothic.ttf'; #'/MyriadPro-Semibold.otf';
        $this->tel_wp_image($telstr);
        /*
             字符串长度对字体，线性关系  11-18,18->9
            */
        $c_len = strlen($telstr);
        $font_size = $this->fontSize;
        /*
        if ($c_len > 11) {
            $font_size = max(9, $font_size - ($c_len - 11));
        }
        */
        $width = $this->width; //strlen($telstr)*11+11;
        $im = imagecreate($width, $font_size + 10);
        $black = ImageColorAllocate($im, 25, 123, 48);
        $white = ImageColorAllocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $white);
        $im = $this->random_imagettftext($im, $font_size, 0, 1, 18, array ($this->colorR, $this->colorG, $this->colorB), $font_file, $telstr, 6);
        imagecolortransparent($im, $white);
        header("Content-type: image/png");
        imagepng($im);
        imagedestroy($im);
    
    }
    private function getPrivateKey()
    {
        return "Revship#(@#!Key" . Revship::lib('config')->getItem('site.licenseNumber');
    }
    /*
     * Display a PNG with particular text
     */
    public function renderImage($text, $encrypted = true, $color = null, $fontSize = null){
        if($color != null)
        {
            if(!is_array($color))
            {
                $color = Revship::lib('color')->hex2rgb($color);
            }
            $this->colorR = $color[0];
            $this->colorG = $color[1];
            $this->colorB = $color[2];
        }
        if($fontSize != null)
        {
            $this->fontSize = $fontSize;
        }
        $key = $this->getPrivateKey();
        if($encrypted)
        {
            $text = $this->tel_decrypt(htmlspecialchars($text), $key);
        }
        $this->generateImage($text);
    }
    public function getImageURL($text, $pathPrefix = '/', $fileExtension = '.png', $color = null, $fontSize = null)
    {
        $key = $this->getPrivateKey();
        if($color != null)
        {
            $query['color'] = $color;
        }
        if($fontSize != null)
        {
            $query['fontSize'] = $fontSize;
        }
        $query['file'] = $this->tel_encrypt($text,$key).$fileExtension;
        $url = $pathPrefix . http_build_query($query);
        return $url;
    }
}
