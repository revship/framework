<?php
class Revship_Transform
{
    /*
     * Enrich sprintf() to support unlimited array param
     */
    public function sprintf($text, array $replaceArray)
    {
        $repString = '';
        foreach($replaceArray as $replace)
        {
            $repString .= ',\''. addslashes($replace) . '\'';
        }
        $mixed = '$mixed = sprintf(\''. addslashes($text) .'\''. $repString .');';
        eval($mixed);
        return $mixed;
    }
    /*
     * Middle ellipsis a long string
     */
    public function middleEllipsisString($string, $limitLength=11 )
    {
        if($limitLength < 7)
        {
            return $string;
        }
        if(strlen($string) <= $limitLength)
        {
            return $string;
        }
        
        $subNum = floor(($limitLength-3)/2);
        $beginStr = substr( $string, 0, $subNum );
        $endStr = substr( $string, -$subNum );
        return $beginStr . '...' . $endStr;
    } 
    /**
     * $ids = '123|432|12' 
     * => array(123,432,12);
     */
    public function turnStringToArray($ids, $separator = '|')
    {
        if ( strstr($ids, $separator) )
        {
            $idsArray = explode($separator, $ids);
            $ids = array();
            foreach ( $idsArray as $val )
            {
                if ( $val) {
                    $ids[] = $val;
                }
            }
        } else {
            $ids = array($ids);
        }
        return $ids;
    }
    
    /**
     * Permalink Character Convert Helper
     */
    public function permalinkCharConvertHelper($name)
    {
        $text=stripslashes(trim($name));
    
        $pattern=array('`','~','!','@','#','$','%','^','*','(',')','_','+','=','{','}','|','[',']','\\',':','"',';','\'','<','>','?',',','.','/');
    
        foreach($pattern as $v)
        {
            $text=str_replace($v,'',$text);
        }
        $text=str_replace(' & ','-and-',$text);
        $text=str_replace('& ','-and-',$text);
        $text=str_replace(' &','-and-',$text);
        $text=str_replace('&','-and-',$text);
        $text=str_replace(' ','-',$text);
        $text=htmlspecialchars($text);
        return $text;
    }
    /**
     * UTF-8 Sub_str
     */
    public function tword($str,$len) {
        $str=strip_tags(trim(str_replace("<br />"," ",$str)));
        if(strlen($str)<$len)
        {
             return $str;
        }
        else
        {
            for($i=0;$i<$len;$i++)
            {
                   $temp_str=substr($str,0,1);
                   if(ord($temp_str) > 127)
                   {
                        $i++;
                        if($i<$len)
                        {
                         $new_str[]=substr($str,0,3);
                         $str=substr($str,3);
                        }
                   }
                   else
                   {
                        $new_str[]=substr($str,0,1);
                       $str=substr($str,1);
                   }
            }
        return join($new_str).'...';
        }
    }
    /**
     * 检查一段utf-8编码的字符串是否为中文
     * @param $sourceString	被检查的字符串
     * @return boolean		如果是，返回true，否则返回false
     */
    public static function isChinese_utf8 ($sourceString)
    {
        return preg_match('/^[\x7f-\xff]+$/', $sourceString);
    }
    /**
     * 获得utf-8编码的字符串的长度
     * @param $sourceString	utf-8编码的字符串
     * @return int			长度
     */
    public static function strlen_utf8 ($sourceString)
    {
        //换行符修改为一字节 和JS判断相一致 修改人：刘必坚 修改时间：2009.2.18
        $str = str_replace("\r\n", " ", $sourceString);
        $str = stripslashes($str);
        return mb_strlen($str, 'utf-8');
    }
    /**
     * 截字(utf8)
     * @param $sourceString 被截取的字符串
     * @param $length		截取的长度
     * @param $offset		开始截取的位置，从0开始
     * @return string		截取得到的字符串
     */
    public function trword ($sourceString, $maxLength, $postFix = '...')
    {
        if ($this->strlen_utf8($sourceString) > $maxLength) {
            return $this->substr_utf8($sourceString, $maxLength, 0) . $postFix;
        }
        return $sourceString;
    }
    /**
     * 截取utf-8编码的字符串
     * @param $sourceString 被截取的字符串
     * @param $length		截取的长度
     * @param $offset		开始截取的位置，从0开始
     * @return string		截取得到的字符串
     */
    public function substr_utf8 ($sourceString, $length, $offset = 0)
    {
        return mb_substr($sourceString, $offset, $length, 'utf-8');
    }
    /**
     * 查找字符串位置
     *
     * @param sring $sourceString 被查找的字符串
     * @param string $needle 	  查找字符串
     * @param int $offset   	  开始查找的位置，从0开始
     * @return 若存在返回字符串位置，否则返回false
     */
    public function strpos_utf8 ($sourceString, $needle, $offset = 0)
    {
        return mb_strpos($sourceString, $needle, $offset, 'utf-8');
    }
    /**
     * 生成一个随机串 
     *
     * @param $length 要反回字符串的长度
     * @return $str 
     */
    public function getRandStr ( $length = 4) {
        
        $letters = 'bcdfghjklmnpqrstvwxyz';
        $vowels = 'aeiou';
        $str = '';
        for($i = 0; $i < $length; ++$i)
        {
            if($i % 2 && mt_rand(0,10) > 2 || !($i % 2) && mt_rand(0,10) > 9)
                $str.=$vowels[mt_rand(0,4)];
            else
                $str.=$letters[mt_rand(0,20)];
        }

        return $str;     
        
    }
}