<?php

class Revship_Ping
{
    /**
     * Get PING time (second)
     * @param string $host   IP address / Host name
     * @return double
     */
    public function getPingTime($host)
    {
        $output = Revship::lib('system')->exec("ping -c 1 {$host}");
        preg_match('/(.*) = ([^\/]*)\//', $output, $matches);
        if(isset($matches[2]) && is_numeric($matches[2]))
        {
            return doubleval($matches[2]);
        }
        return false;
    }
    /**
     * Get the time of getting the Http Header (second)
     * @param string $url  
     * @return double
     */
    public function getHttpTime($url)
    {
        $bm = Revship::lib('benchmarking');
        $curl = Revship::lib('curl');
        $bm->mark('getHttpTimeStart-'.$url);
        $curl->getHttpCode($url,2);
        $bm->mark('getHttpTimeEnd-'.$url);
        return $bm->getDurationTime('getHttpTimeStart-'.$url, 'getHttpTimeEnd-'.$url);
    }
    
    /**
     * Sort multi host
     * @param array $hostArray array('us1.geo.revship.com', 'jp1.geo.revship.com')
     * @return array( array('jp1.geo.revship.com','0.021')
     *                         array('us1.geo.revship.com','0.325') )
     */
    public function sortHostTime($hostArray)
    {
        $method = 'httpCode';
        $timeHostArray = array();
        foreach ($hostArray as $host)
        {
            if($method == 'ping')
            {
                $time = $this->getPingTime($host);
            }
            else
            {
                $time = $this->getHttpTime($host);
            }
            $time = (string) $time;
            $timeHostArray[$time] = $host;
        }
        ksort($timeHostArray);
        $outputArray = array();
        foreach ($timeHostArray as $time => $host)
        {
            $outputArray[] = array($time, $host);
        }
        return $outputArray;
    }
    /**
     * Get Fastest Host
     * @param array $hostArray array('us1.geo.revship.com', 'jp1.geo.revship.com')
     * @return string 'jp1.geo.revship.com'
     */
    public function getFastestHost($hostArray)
    {
        $testHosts = $this->sortHostTime($hostArray);
        return $testHosts[0][1];
    }
}
