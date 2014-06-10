<?php 


if (!function_exists('imagecreatefrombmp'))
{
    function imagecreatefrombmp($fname)
    {
        $buf=@file_get_contents($fname);
        if(strlen($buf)<54)   return   false;
        $file_header=unpack("sbfType/LbfSize/sbfReserved1/sbfReserved2/LbfOffBits",substr($buf,0,14));
        if($file_header["bfType"]!=19778)   return   false;
        $info_header=unpack("LbiSize/lbiWidth/lbiHeight/sbiPlanes/sbiBitCountLbiCompression/LbiSizeImage/lbiXPelsPerMeter/lbiYPelsPerMeter/LbiClrUsed/LbiClrImportant",substr($buf,14,40));

        if($info_header["biBitCountLbiCompression"]==2)   return   false;

        $line_len=round($info_header["biWidth"]*$info_header["biBitCountLbiCompression"]/8);
        $x=$line_len%4;
        if($x>0)   $line_len+=4-$x;

        $img=imagecreatetruecolor($info_header["biWidth"],$info_header["biHeight"]);
        switch($info_header["biBitCountLbiCompression"]){
            case   4:
                $colorset=unpack("L*",substr($buf,54,64));
                for($y=0;$y<$info_header["biHeight"];$y++){
                    $colors=array();
                    $y_pos=$y*$line_len+$file_header["bfOffBits"];
                    for($x=0;$x<$info_header["biWidth"];$x++){
                        if($x%2)
                        $colors[]=$colorset[(ord($buf[$y_pos+($x+1)/2])&0xf)+1];
                        else
                        $colors[]=$colorset[((ord($buf[$y_pos+$x/2+1])>>4)&0xf)+1];
                    }
                    imagesetstyle($img,$colors);
                    imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
                }
                break;
            case   8:
                $colorset=unpack("L*",substr($buf,54,1024));
                for($y=0;$y<$info_header["biHeight"];$y++){
                    $colors=array();
                    $y_pos=$y*$line_len+$file_header["bfOffBits"];
                    for($x=0;$x<$info_header["biWidth"];$x++){
                        $colors[]=$colorset[ord($buf[$y_pos+$x])+1];
                    }
                    imagesetstyle($img,$colors);
                    imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
                }
                break;
            case   16:
                for($y=0;$y<$info_header["biHeight"];$y++){
                    $colors=array();
                    $y_pos=$y*$line_len+$file_header["bfOffBits"];
                    for($x=0;$x<$info_header["biWidth"];$x++){
                        $i=$x*2;
                        $color=ord($buf[$y_pos+$i])|(ord($buf[$y_pos+$i+1])<<8);
                        $colors[]=imagecolorallocate($img,(($color>>10)&0x1f)*0xff/0x1f,(($color>>5)&0x1f)*0xff/0x1f,($color&0x1f)*0xff/0x1f);
                    }
                    imagesetstyle($img,$colors);
                    imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
                }
                break;
            case   24:
                for($y=0;$y<$info_header["biHeight"];$y++){
                    $colors=array();
                    $y_pos=$y*$line_len+$file_header["bfOffBits"];
                    for($x=0;$x<$info_header["biWidth"];$x++){
                        $i=$x*3;
                        $colors[]=imagecolorallocate($img,ord($buf[$y_pos+$i+2]),ord($buf[$y_pos+$i+1]),ord($buf[$y_pos+$i]));
                    }
                    imagesetstyle($img,$colors);
                    imageline($img,0,$info_header["biHeight"]-$y-1,$info_header["biWidth"],$info_header["biHeight"]-$y-1,IMG_COLOR_STYLED);
                }
                break;
            default:
                return   false;
                break;
        }
        return   $img;
    }
}





class Revship_Image
{
    protected $thumbLargeThenPic = false;
    protected $_aInfo = array();
    protected $_aTypes = array('', 'gif', 'jpg', 'png');    
    protected $_hImg;    
    protected $nW;
    protected $nH;
    protected $sType;
    protected $sMimeType;
    protected $sPath;
    
    protected $needCropSizeArray = array('s','m','b');
    
    
    public function deleteTempFile($fileName)
    {
        return Revship::lib('file')->unlink($fileName);
    }
    /**
     * Upload file to upload/temp folder
     */
    public function uploadToTemp()
    {
        $targetPath = UPLOAD_TEMP_PATH;
        if(Revship::lib('file')->isWritable($targetPath))
        {
            if (!empty($_FILES)) {
                    $tempFile = $_FILES['Filedata']['tmp_name'];
                    $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
                    $fileTypes  = str_replace(';','|',$fileTypes);
                    $typesArray = explode('|',$fileTypes);
                    $fileParts  = pathinfo($_FILES['Filedata']['name']);
                    $fileParts['extension'] = strtolower($fileParts['extension']);
                    if( !in_array($fileParts['extension'], $this->_aTypes) )
                    {
                        Revship::end('Extension not allowed');
                    }
                    $targetFile =  str_replace('//','/',$targetPath) . time() . rand(1000, 9999) . '.'.$fileParts['extension'];
                    if (in_array($fileParts['extension'],$typesArray)) {
                        // Uncomment the following line if you want to make the directory if it doesn't exist
                        //mkdir(str_replace('//','/',$targetPath), 0777, true);
                        move_uploaded_file($tempFile,$targetFile);
                        return str_replace($targetPath, '' ,$targetFile);
                    } else {
                        return false;
                    }
            }
        }
        else
        {
            return false; // is not writable.
        }
    }
    
    public function moveAndGenerateAllThumbnails($tempFile, $toFolder)
    {
        // Thumbnails are dynamic generated now.
        $tempFile = self::moveAndGenerateBiggestSize($tempFile, $toFolder);
        return true;
        /*
        $tempFile = self::moveAndGenerateThumbnail($tempFile, $toFolder, 'o');
        $tempFile = self::moveAndGenerateThumbnail($tempFile, $toFolder, 'l');
        $tempFile = self::moveAndGenerateThumbnail($tempFile, $toFolder, 'b');
        $tempFile = self::moveAndGenerateThumbnail($tempFile, $toFolder, 'm');
        $tempFile = self::moveAndGenerateThumbnail($tempFile, $toFolder, 's');
        return true;
        */
    }
    public function moveAndGenerateBiggestSize($tempFile, $toFolder)
    {
        $fileName = basename($tempFile);
        $fileName = preg_replace('/(._)/', '', $fileName);
        //$fileName = str_replace('o_', '', $fileName);
        $toFolder = str_replace("//", "/", $toFolder . DS);
        $width = Revship::lib('config')->getItem('site.photoSizeWidth') ? Revship::lib('config')->getItem('site.photoSizeWidth') : 2048 ;
        $height = Revship::lib('config')->getItem('site.photoSizeHeight') ? Revship::lib('config')->getItem('site.photoSizeHeight') : 1536 ;
        $this->createThumbnail($tempFile, $toFolder . $fileName, $width, $height);
        return $toFolder . $fileName;
    }
    public function moveAndGenerateThumbnail($tempFile, $toFolder, $size)
    {
        $fileName = basename($tempFile);
        $fileName = preg_replace('/(._)/', '', $fileName);
        //$fileName = str_replace('o_', '', $fileName);
        $toFolder = str_replace("//", "/", $toFolder . DS);
        switch ($size)
        {
            case 's':
                $width = Revship::lib('config')->getItem('site.photoSizeSWidth') ? Revship::lib('config')->getItem('site.photoSizeSWidth') : 20 ;
                $height = Revship::lib('config')->getItem('site.photoSizeSHeight') ? Revship::lib('config')->getItem('site.photoSizeSHeight') : 20 ;
                break;
            case 'm':
                $width = Revship::lib('config')->getItem('site.photoSizeMWidth') ? Revship::lib('config')->getItem('site.photoSizeMWidth') : 40 ;
                $height = Revship::lib('config')->getItem('site.photoSizeMHeight') ? Revship::lib('config')->getItem('site.photoSizeMHeight') : 40 ;
                break;
            case 'b':
                $width = Revship::lib('config')->getItem('site.photoSizeBWidth') ? Revship::lib('config')->getItem('site.photoSizeBWidth') : 100 ;
                $height = Revship::lib('config')->getItem('site.photoSizeBHeight') ? Revship::lib('config')->getItem('site.photoSizeBHeight') : 100 ;
                break;
            case 'l':
                $width = Revship::lib('config')->getItem('site.photoSizeLWidth') ? Revship::lib('config')->getItem('site.photoSizeLWidth') : 250 ;
                $height = Revship::lib('config')->getItem('site.photoSizeLHeight') ? Revship::lib('config')->getItem('site.photoSizeLHeight') : 190 ;
                break;
            case 'o':
                $width = Revship::lib('config')->getItem('site.photoSizeOWidth') ? Revship::lib('config')->getItem('site.photoSizeOWidth') : 570 ;
                $height = Revship::lib('config')->getItem('site.photoSizeOHeight') ? Revship::lib('config')->getItem('site.photoSizeOHeight') : 410 ;
                break;
            default:
                return false;
                break;
        }
        if( in_array($size, $this->needCropSizeArray) )
        {
            $this->resizeImage($tempFile, $toFolder. $size . '_' . $fileName, $width, $height, true);
        }
        else 
        {
            $this->createThumbnail($tempFile, $toFolder. $size . '_' . $fileName, $width, $height);
        }
        return $toFolder. $size . '_' . $fileName;
    }
    
    
    public function getNewSize($sImage = null, $iMaxHeight, $iMaxWidth, $iWidth = 0, $iHeight = 0)
    {
        if (!$iWidth && !$iHeight)
        {
            list($iWidth, $iHeight) = getimagesize($sImage);
        }
        
        $k = "";        
        //get scaling factor
        if ($iMaxWidth && $iMaxHeight && $iWidth && $iHeight)
        {
            $kX = $iMaxWidth / $iWidth;
            $kY = $iMaxHeight / $iHeight;
            $k = min($kX, $kY);
        }
        elseif ($iMaxHeight && $iHeight)
        {
            $k = $iMaxHeight / $iHeight;
        }
        elseif ($iMaxWidth && $iWidth)
        {
            $k = $iMaxWidth / $iWidth;
        }
    
        //correct scaling factor
        if (((0 >= $k) || ($k > 1)))
        {
            $k = 1;
        }
    
        $iHeight *= $k;
        $iWidth *= $k;        
        
        return array(round($iHeight), round($iWidth));
    }
    /**
     * Crop an image
     * return: FALSE on failure, NULL on success
     * access: public
     * bool cropImage (string $sImage, string $sDestination, int $iWidth, int $iHeight, int $iStartWidth, int $iStartHeight, int $iScale)
     * string $sImage: Full path to the image we are working with
     * string $sDestination: Full path where the new image will be placed
     * int $iWidth: Width of the working image
     * int $iHeight: Height of the working image
     * int $iStartWidth: Starting point of where we are cropping the image (X)
     * int $iStartHeight: Starting point of where we are cropping the image (Y)
     * int $iScale: Width/Height of what the image should be scalled to
     */
    public function cropImage($sImage, $sDestination, $iWidth, $iHeight, $iStartWidth, $iStartHeight, $iScale)
    {
        if (!$this->_load($sImage))
        {
            return false;
        }        
        
        $iScale = ($iScale / $iWidth);
        
        $iNewImageWidth = ceil($iWidth * $iScale);
        $iNewImageHeight = ceil($iHeight * $iScale);
        
        switch ($this->_aInfo[2])
        {
            case 1:
                $hFrm = @imageCreateFromGif($this->sPath);
                break;
            case 3:
                $hFrm = @imageCreateFromPng($this->sPath);
                break;
            default:
                $hFrm = @imageCreateFromJpeg($this->sPath);               
                break;
        }        
        
        $hTo = imagecreatetruecolor($iNewImageWidth, $iNewImageHeight);
        
        switch($this->sType)
        {
            case 'gif':
                $iBlack = imagecolorallocate($hTo, 0, 0, 0);
                imagecolortransparent($hTo, $iBlack);
                break;        
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                imagealphablending($hTo, true);
            break;            
            case 'png':
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);
            break;
        }            
        
        imageCopyResampled($hTo, $hFrm, 0, 0, $iStartWidth, $iStartHeight, $iNewImageWidth, $iNewImageHeight, $iWidth, $iHeight);    
        
        switch ($this->sType)
        {
                case 'gif':
                if(!$hTo)
                {
                        @copy($this->sPath, $sDestination);
                }
                else
                {
                    @imagegif($hTo, $sDestination);
                }
                break;
            case 'png':
                    @imagepng($hTo, $sDestination);
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);                   
                break;
            default:
                    @imagejpeg($hTo, $sDestination);
                    break;
        }        
        
        @imageDestroy($hTo);        
        @imageDestroy($hFrm);
    }

    
    
    public static function resizeImage( $src, $dest, $w, $h, $crop=false)
    {
        if( file_exists($src)  && isset($dest) )
        {
            $src_size   = getimagesize($src);
            $src_extension = $src_size[2];
            $src_w = $src_size[0];
            $src_h = $src_size[1];
            if( $crop )
            {
                $ratio = min($src_w/$w, $src_h/$h);
                $src_w = $w * $ratio;
                $src_h = $h * $ratio;
            }
            else
            {
                $ratio = min($w/$src_w, $h/$src_h);
                $w = $src_w*$ratio;
                $h = $src_h*$ratio;
            }
            //            die("$ratio,$src_w,$src_h,$w,$h");
        }
        $dest_image = imagecreatetruecolor($w,$h);
        switch( $src_extension )
        { 
            case 1:
                $srcImage = imagecreatefromgif($src);
                break;
            case 2:
                $srcImage = imagecreatefromjpeg($src);
                break;
            case 3:
                $srcImage = imagecreatefrompng($src);
                break;
            case 6:
                $srcImage = imagecreatefrombmp($src);
                break;
        }
        imagecopyresampled($dest_image, $srcImage, 0, 0, 0, 0,$w,$h,$src_w,$src_h);
        switch ( $src_extension )
        {
            case 1:
                imagegif($dest_image,$dest);
                break;
            case 2:
            case 6:
                imagejpeg($dest_image,$dest,100);
                break;
            case 3:
                imagepng($dest_image,$dest);
                break;
                imagedestroy($dest_image);
        }
        return true;
    }
    
    
    
    public function createThumbnail($sImage, $sDestination, $nMaxW, $nMaxH, $bRatio = true)
    {        
        if (!$this->_load($sImage))
        {
            return false;
        }        
        
        if ($bRatio)
        {
            list($nNewW, $nNewH) = $this->_calcSize($nMaxW, $nMaxH);    
        }
        else 
        {
            $nNewW = $nMaxW;
            $nNewH = $nMaxH;
        }
        
        if ($this->nW < $nNewW ||  $this->nH < $nNewH || ($this->nW == $nNewW && $this->nH == $nNewH))
        {
            @copy($this->sPath, $sDestination);
            
            return true;    
        }
        
        if (function_exists('memory_get_usage') AND $sMemoryLimit = @ini_get('memory_limit') AND $sMemoryLimit != -1)
        {
            $iMemoryLimit = (int) $sMemoryLimit;
            $iMemoryUsage = memory_get_usage();
            $iFreeMemory = $iMemoryLimit - $iMemoryUsage;            
            $iTotalMemory = $this->nW * $this->nH * ($this->_aInfo[2] == 2 ? 5 : 2) + 7372.8 + sqrt(sqrt($this->nW * $this->nH));
            $iTotalMemory += 166000;
            
            if ($iFreeMemory > 0 AND $iTotalMemory > $iFreeMemory AND $iTotalMemory <= ($iMemoryLimit * 3) && !PHPFOX_SAFE_MODE)
            {
                ini_set('memory_limit', $iMemoryLimit + $iTotalMemory);

                $sMemoryLimit = @ini_get('memory_limit');
                $iMemoryLimit = (int) $sMemoryLimit;
                $iMemoryUsage = memory_get_usage();
                $iFreeMemory = $iMemoryLimit - $iMemoryUsage;
            }            
            
            if ($iFreeMemory > 0 AND $iTotalMemory > $iFreeMemory)
            {
                return new Revship_Exception(500,'Ran out of memory.');
            }
        }        
        switch ($this->_aInfo[2])
        {
            case 1:
                $hFrm = imageCreateFromGif($this->sPath);
                break;
            case 3:
                $hFrm = imageCreateFromPng($this->sPath);
                break;
            default:
                $hFrm = imageCreateFromJpeg($this->sPath);               
                break;
        }
        if ((int) $nNewH === 0)
        {
                $nNewH = 1;
        }
        if ((int) $nNewW === 0)
        {
                $nNewW = 1;
        }
        $hTo = imagecreatetruecolor($nNewW, $nNewH);
        switch($this->sType)
        {
            case 'gif':
                $iBlack = imagecolorallocate($hTo, 0, 0, 0);
                imagecolortransparent($hTo, $iBlack);
                break;            
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                imagealphablending($hTo, true);
            break;            
            case 'png':
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);
            break;
        }

        if ($this->thumbLargeThenPic === false && $this->nH <= $nNewH && $this->nW <= $nNewW)
        {
            $hTo = $hFrm;
        }
        else
        {
            if($hFrm)
            {
                imageCopyResampled($hTo, $hFrm, 0, 0, 0, 0, $nNewW, $nNewH, $this->nW, $this->nH);                
            }
        }        
        
        switch ($this->sType)
        {
            case 'gif':
                if(!$hTo)
                {
                    @copy($this->sPath, $sDestination);
                }
                else
                {
                    @imagegif($hTo, $sDestination);
                }
            break;
            case 'png':
                imagepng($hTo, $sDestination);
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);
            break;
            default:
                @imagejpeg($hTo, $sDestination);
                break;
        }        
        
        @imageDestroy($hTo);        
        @imageDestroy($hFrm);        
    }
    
    public function rotate($sImage, $sCmd)
    {
        if (!$this->_load($sImage))
        {
            return false;
        }            
        
        switch ($this->_aInfo[2])
        {
            case 1:
                $hFrm = @imageCreateFromGif($this->sPath);
                break;
            case 3:
                $hFrm = @imageCreateFromPng($this->sPath);
                break;
            default:
                $hFrm = @imageCreateFromJpeg($this->sPath);               
                break;
        }        
        
        @unlink($this->sPath);
        
         $wid = imagesx($hFrm);
         $hei = imagesy($hFrm);
         $im2 = imagecreatetruecolor($hei,$wid);
         
        switch($this->sType)
        {        
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                imagealphablending($im2, true);
            break;            
            case 'png':
                imagealphablending($im2, false);
                imagesavealpha($im2, true);
            break;
        }             
        
         for($i = 0;$i < $wid; $i++)
         {
             for($j = 0;$j < $hei; $j++)
              {
                  $ref = imagecolorat($hFrm,$i,$j);
                  if ($sCmd == 'right')
                  {
                       imagesetpixel($im2,($hei - 1) - $j,$i,$ref);
                  }
                  else 
                  {
                       imagesetpixel($im2,$j, $wid - $i,$ref);
                  }
              }
         }
        
        switch ($this->sType)
        {
            case 'gif':
                @imagegif($im2, $this->sPath);
            break;
            case 'png':
                @imagepng($im2, $this->sPath);
                imagealphablending($im2, false);
                imagesavealpha($im2, true);                
            break;
            default:
                @imagejpeg($im2, $this->sPath);
                break;
        }       
        
        imagedestroy($hFrm); 
        imagedestroy($im2);
    }
    
    protected function _load($sPath)
    {
        $this->sPath = $sPath;
        if ($this->_aInfo = @getImageSize($sPath))
        {            
            if (!isset($this->_aTypes[$this->_aInfo[2]]))
            {
                return false;
            }
            $this->nW = $this->_aInfo[0];
            $this->nH = $this->_aInfo[1];
            $this->sType = $this->_aTypes[$this->_aInfo[2]];
            $this->sMimeType = $this->_aInfo['mime'];
            return true;
        }
        return false;
    }
    
    protected function _destroy()
    {
            $this->sPath = null;
            $this->_aInfo = array();
            $this->nW = null;
            $this->nH = null;
            $this->sType = null;
            $this->sMimeType = null;
    }
    
    protected function _hex2rgb($sHex)
    {
        $iRed = substr($sHex, 0, 2);
        $iGreen = substr($sHex, 2, 2);
        $iBlue = substr($sHex, 4, 2);
        $iRed = hexdec($iRed);
        $iGreen = hexdec($iGreen);
        $iBlue = hexdec($iBlue);
        return array($iRed, $iBlue, $iGreen);
    }    
        
    /** 
     * Calculates size for resizing.
     * 
     * @param int $nMaxW  maximum width
     * @param int $nMaxH  maximum height
     * @return array new size (width, height)
     */
    protected function _calcSize($nMaxW, $nMaxH)
    {
        $w  = $nMaxW;
        $h  = $nMaxH;
        if ($this->nW > $nMaxW)
        {
            $w  = $nMaxW;
            $h  = floor($this->nH * $nMaxW/$this->nW);
            if ($h > $nMaxH)
            {
              $h  = $nMaxH;
              $w  = floor($this->nW * $nMaxH/$this->nH);
            }
        }
        elseif ($this->nH > $nMaxH)
        {
            $h  = $nMaxH;
            $w  = floor($this->nW * $nMaxH/$this->nH);
        }
        return array($w, $h);
    } 
    
}
