<?php
class Revship_File
{
    /**
     * Checks if path is writable
     *
     * @param string $sPath Path to file or directory
     * @return boolean
     *
     */
    public function isWritable($sPath, $bForce = false)
    {        
        clearstatcache();
        //////
        return is_writable($sPath);
        //////
        if ($bForce === false)
        {
            if (!is_writable($sPath))
            {
                if (!stristr(PHP_OS, "win"))
                {
                    return false;
                }
            }
        }
        
        if ($bForce === true)
        {
            //Phpfox_Error::skip(true);
        }

        /**
         * Checking if writable on windows OS
         */
        if (stristr(PHP_OS, "win") || $bForce === true)
        {
            /**
             * need to check whether we can really create files in this directory or not
             */
            if (is_dir($sPath))
            {
                /**
                 * Trying to create a new file
                 */
                $fp = @fopen($sPath . 'win-test.txt', 'w');                
                if (!$fp)
                {
                    if ($bForce === true)
                        {
                        //Phpfox_Error::skip(false);    
                        }
                        return false;
                }
                if (!@fwrite($fp, 'test'))
                {
                    if ($bForce === true)
                        {
                        //Phpfox_Error::skip(false);    
                        }
                        return false;
                }
                fclose($fp);
                /**
                 * clean up after ourselves
                 */
                unlink($sPath . 'win-test.txt');
            } 
            else
            {
                if (!file_exists($sPath))
                {
                    if ($bForce === true)
                        {
                            //Phpfox_Error::skip(false);    
                        }                    
                        return false;
                }

                $sContent = @file_get_contents($sPath);
                if (!$fp = @fopen($sPath, 'w'))
                {
                    if ($bForce === true)
                        {
                            //Phpfox_Error::skip(false);    
                        }                    
                        return false;
                }
                
                if (!@fwrite($fp, $sContent))
                {
                    if ($bForce === true)
                        {
                            //Phpfox_Error::skip(false);    
                        }                    
                        return false;
                }
                
                fclose($fp);
            }
        }
        
        if ($bForce === true)
            {
                //Phpfox_Error::skip(false);    
        }        
        
        return true;
    }
    
    /**
     * @todo Needs further testing
     *
     */
    public function getTempDir()
    {        
        if (!empty($_ENV['TMP'])) 
        {
            $sTempDir = $_ENV['TMP'];
        } 
        elseif (!empty($_ENV['TMPDIR'])) 
        {
            $sTempDir = $_ENV['TMPDIR'];
        } 
        elseif (!empty($_ENV['TEMP'])) 
        {
            $sTempDir = $_ENV['TEMP'];
        } 
        else 
        {
            if (function_exists('sys_get_temp_dir'))
            {
                $sTempDir = sys_get_temp_dir();
            }
            else 
            {
                $sTempFile = tempnam(md5(uniqid(rand(), true)), '');
                if ($sTempFile)
                {
                    $sTempDir = realpath(dirname($sTempFile));
                    
                    unlink($sTempFile);                
                }
                else
                {
                    return false;
                }                
            }
        }    

        return rtrim($sTempDir, DS) . DS;    
    }
    
    
    public function unlink($sSrc)
    {
        if (@unlink($sSrc))
        {
            return true;
        }
        
        return false;
    }

    /**
     * How big does upload file can be accepted
     */
    public function getLimit($iMaxSize = 10485760000)
    {
            $iUploadMaxFileSize = (ini_get('upload_max_filesize') * 1048576);
            $iPostMaxSize = (ini_get('post_max_size') * 1048576);
            
            if ($iUploadMaxFileSize < ($iMaxSize * 1048576))
            {
                return ini_get('upload_max_filesize');
            }
            
            if ($iPostMaxSize < ($iMaxSize * 1048576))
            {
                return ini_get('post_max_size');
            }
            
            return $iMaxSize . 'MB';
    }
    
    /**
     * Delete a directory
     */
    public function deleteDirectory($dir)
    {
        if(is_dir($dir)) 
        {
                if($dh = opendir($dir)) 
                {
                    while(($file = readdir($dh)) !== false) 
                    {
                        if($file != '.' && $file != '..') 
                        {
                            if(is_dir($dir . '/' . $file)) 
                            {
                                $this->deleteDirectory($dir . '/' . $file);
                            } 
                            else
                            {
                                unlink($dir . '/' . $file);
                             }
                        }
                    }
                }
                closedir($dh);
                rmdir($dir);
        }
    }
    
    /**
     * Make directory
     */
    public function mkdir($sDir, $mode = 0644, $bRecurse = false)
    {        
        if ($bRecurse === true)
        {            
            $aParts = explode(DS, trim($sDir, DS));
            $sParentDirectory = ($this->isWindows() ? '' : DS);
            
            foreach ($aParts as $sDir)
            {            
                if (!is_dir($sParentDirectory . $sDir))
                {
                    mkdir($sParentDirectory . $sDir , $mode);              
                }
                $sParentDirectory .= $sDir . DS;
            }            
        }
        else 
        {            
            mkdir($sDir, $mode);
        }
    }    
    
    /**
     * Rename
     */
    public function rename($sSrc, $sDest)
    {
        if (@rename($sSrc, $sDest))
        {
            return true;
        }
        return false;
    }
    
    
    
    /**
     * Is System Windows?
     */
    public function isWindows()
    {
        return (PHP_OS == 'WINNT' || PHP_OS == 'WIN32' || PHP_OS == 'Windows');
    }
    
    
    public function getFiles($sDir, $requiredExtension=null)
    {
        $aFiles = array();
        if ($hDir = @opendir($sDir))
        {
            while (false !== ($sFile = readdir($hDir)))
               {
                    if ($sFile == '.' || $sFile == '..' || $sFile == '.svn' || $sFile == '.svn-ignore' || $sFile == 'index.html')
                    {
                        continue;
                    }
                    if ($requiredExtension)
                    {
                        if(! is_array($requiredExtension))
                        {
                            $requiredExtension = array($requiredExtension);
                        }
                        foreach ($requiredExtension as $extension)
                        {
                             if(strpos($sFile, $extension) !== false)
                             {
                                 $aFiles[] = $sFile;
                                 continue;
                             } 
                        }
                    }
                    else
                    {
                       $aFiles[] = $sFile;
                    }
               }
               closedir($hDir);
               return $aFiles;
        }
        return false;
    }
    

    public function getAllFiles($sDir, $bRecurse = false)
    {
        static $aFiles = array();
        
        if ($bRecurse === false)
        {
            $aFiles = array();
        }
        
        $hDir = opendir($sDir);
        while ($sFile = readdir($hDir))
        {
            if ($sFile == '.' || $sFile == '..' || $sFile == '.svn')
            {
                continue;
            }
            
            $sNewDir = rtrim($sDir, DS) . DS . $sFile;
            
            if (is_dir($sNewDir))
            {
                $this->getAllFiles($sNewDir, true);
            }
            else 
            {
                $aFiles[] = $sNewDir;
            }    
        }
        closedir($hDir);
        
        return $aFiles;    
    }
    
    public static function filesize($iSize, $iPrecision = 2)
    {
        if (!is_numeric($iSize))
        {
            return $iSize;
        }

        if (!is_numeric($iPrecision))
        {
            $iPrecision = 2;
        }

        $sSize   = '';
        $fSize   = 0;
        $sSuffix = '';

        if ($iSize >= 1073741824)
        {
            $fSize = $iSize / 1073741824;
            $sSuffix = 'Gb';
        }
        elseif (($iSize >= 1048576) && ($iSize < 1073741824))
        {
            $fSize = $iSize / 1048576;
            $sSuffix = 'Mb';
        }
        elseif (($iSize >= 1024) && ($iSize < 1048576))
        {
            $fSize = $iSize / 1024;
            $sSuffix = 'kb';
        }
            else
            {
                $fSize = $iSize;
                $sSuffix = 'b';
            }
            $sSize = round($fSize, $iPrecision);
            $sSize .= ' '.$sSuffix;
            
            return $sSize;
    }

    public function write($sFile, $sData, $sMode = 'w')
    {
        if (file_exists($sFile))
        {
            unlink($sFile);
        }        
        
        if ($hFile = fopen($sFile, $sMode))
        {
            fwrite($hFile, trim($sData));
            fclose($hFile);        
            
            return true;
        }        
        
        return false;
    }
    
    public function read($sFile)
    {
        $content = null;
        $file = fopen($sFile,"r");
        while(! feof($file) )
        {            
            $content .= fgets($file);
        }            
        fclose($file);
        return $content;
    }
    
    
    /**
     * @todo Make the mime type check a method for future use for other methods
     *
     * @param unknown_type $sFile
     * @param unknown_type $sName
     * @param unknown_type $sMimeType
     * @param unknown_type $sFileSize
     * @param unknown_type $iServerId
     */
    public function forceDownload($sFile, $sName, $sMimeType = '', $sFileSize = '', $iServerId = 0) 
    {        
        // required for IE  
        if(ini_get('zlib.output_compression')) 
        {
            ini_set('zlib.output_compression', 'Off'); 
        }    
        
        if (!$sMimeType)
        {
             if (function_exists('mime_content_type'))
             {
                 $sMimeType = mime_content_type($sFile);
             }
             else 
             {             
                if (strtolower(PHP_OS) == 'linux')
                {
                    $sMimeType = trim(exec('file -bi ' . escapeshellarg($sFile)));
                }
                 else
                 {
                     // get the file mime type using the file extension  
                     switch(strtolower(substr(strrchr($sFile,'.'), 1)))  
                     {  
                        case 'pdf': 
                            $sMimeType = 'application/pdf'; 
                            break;  
                        case 'zip': 
                            $sMimeType = 'application/zip'; 
                            break;  
                        case 'jpeg':  
                        case 'jpg': 
                            $sMimeType = 'image/jpg'; 
                            break;  
                        default: 
                            $sMimeType = 'application/force-download';  
                            // $sMimeType = 'application/octet-stream';
                     }
                 }
             }
        }        

        // Make sure there's not anything else left
        ob_clean();
        /*
        if ($iServerId && !file_exists($sFile))
        {
                    $sServer = Phpfox::getLib('request')->getServerUrl($iServerId);
                    $sFileServer = $sServer . '/' .str_replace(PHPFOX_DIR, '', $sFile);
                    $this->copy($sFileServer, $sFile);
        }
        */
        // Start sending headers
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: " . $sMimeType);
        header("Content-Length: " . ($sFileSize ? $sFileSize : filesize($sFile)));
        header("Content-Disposition: attachment; filename=\"" . $sName . "\";" );
        // Send data
        readfile($sFile);
        exit;
    }            
    
    /**
     * Copy 
     */
    public function copy($sSrc, $sDest)
    {
        if (@copy($sSrc, $sDest))
        {
            return true;
        }
        return false;
    }
}
