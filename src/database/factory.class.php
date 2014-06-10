<?PHP
/**
 * Database Factory Handler Class
 *
 */
class Revship_Database_Factory
{
    /**
     * Create database handler
     * 
     * @param string    $database DB name
     * @param int       $mode     Host
     * @param int       $encoding Encoding
     * @return	DBMysqli	 Handler
     */
    public static function createDb()
    {
        // Get DB data from config
        
        // Not Ultimate Package. Force rewrite database setting to 'Prefered' one.
        /*
        if( Revship::getPackage() != Revship::PACKAGE_ULTIMATE )
        {   
            $dbConfig = Revship::lib('database')->makeSingleDbConfigPreferedOnly();
        }
        else
        {
            $dbConfig = Revship::lib('database')->makeMultiDbConfig();
        }*/
        Revship::lib('config')->clearCache('databases');
        $dbConfig = Revship::lib('config')->getItem('database');
        if(!isset($dbConfig['driver']))
        {
            throw new Exception('DB Driver is not set:'.print_r($dbConfig,true));    
        }
        switch ( $dbConfig['driver'] )
        {
            case 'mysql':
                $dbConn = Revship::lib('database.driver.mysql',$dbConfig);
                break;
                /*
            case 'mysqli':
                $dbConn = Revship::lib('database.driver.mysqli')->getInstance();
                $dbConn->connect($dbConfig);
                break;
                */
            default:
                $dbConn = null;
                break;
        }

        return $dbConn;
    }
}
