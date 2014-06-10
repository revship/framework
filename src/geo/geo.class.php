<?php
final class Revship_Geo
{
    
    const GEO_HOST_DEFAULT = 'geo.revship.com';
    const TYPE_CONTINENT = 'continent';
    const TYPE_COUNTRY = 'country';
    const TYPE_ADMIN1 = 'admin1';
    const TYPE_CONTINENT_TO_COUNTRY = 'continent-country';
    const TYPE_COUNTRY_TO_ADMIN1 = 'countrycode-admin1';
    const TYPE_COUNTRY_ADMIN1_TO_CITIES = 'country_admin1-cities';
    const TYPE_LOCATION_BY_ID = 'locationById';
    const TYPE_IP = 'ip';
    const CONNECTION_TIMEOUT_SECONDS = 10;
    const CONFIG_IP_TYPE = 'string'; // 'long' OR 'string'  e.g.  127.0.0.1
    
    private $geoHostArray = array(
        //'jp1.geo.revship.com',
        //'us1.geo.revship.com',
        self::GEO_HOST_DEFAULT,
    );
    private $geoUrl = null;
    private $ipUrl = null;
    
    public function __construct()
    {
        $geoHost = Revship::lib('config')->getItem('site.geoHost');
        if(!$geoHost)
        {
            $geoHost = Revship::lib('ping')->getFastestHost($this->geoHostArray);
            if(!$geoHost)
            {
                $geoHost = self::GEO_HOST_DEFAULT;
            }
            Revship::lib('config')->setSingleItem('site.geoHost',$geoHost);
        }
        $this->geoUrl = "http://$geoHost/?domain=" . urlencode(Revship_Licenser::genLicenseDomainFromRealDomain()) . "&license=" . Revship::lib('config')->getItem('site.licenseNumber');
        $this->ipUrl = "http://$geoHost/ip/?domain=" . urlencode(Revship_Licenser::genLicenseDomainFromRealDomain()) . "&license=" . Revship::lib('config')->getItem('site.licenseNumber');
    }
    public function getServerSpeedList()
    {
        return Revship::lib('ping')->sortHostTime($this->geoHostArray);
    }
    public function getListArray( $type = self::TYPE_CONTINENT , $query = '', $scale = '', $host = null)
    {
        if(!$host)
        {
            $host = $this->geoUrl;
        }
        $url = $host . "&type=" . $type . "&query=" . $query . "&scale=" . $scale;
        $curl = Revship::lib('curl');
        $curl->mTimeout = self::CONNECTION_TIMEOUT_SECONDS;
        $returns = $curl->get($url);
        @$array =  json_decode($returns,true);
        if(isset($array) && is_array($array))
        {
            return $array;
        }
        else
        {
            return array();
        }
    }
    /**
     * Get Visitor's IP information from Revship Online Service
     */
    public function getVisitorIpInfo()
    {
        if( self::CONFIG_IP_TYPE == 'string')
        {
            $ip = Revship::lib('http')->getIp(false);
        }
        else 
        {
            $ip = Revship::lib('http')->getIp(true);
        }
        $returnArray = $this->getListArray(self::TYPE_IP, $ip, null, $this->ipUrl);
        return $returnArray;
    }
    
}
