<?PHP
/**
 * Database Abstract Base Class
 *
 */

abstract class Revship_Database_Abstract
{
    /**
     * Connect
     * @param string $host    
     * @param string $userName 
     * @param string $passwd   
     * @param string $database - use database
     * @param int    $port     
     * @return bool            - connected?        
     */
    abstract public function connect();
    /**
     * Disconnect
     */
    abstract public function disconnect();
    /**
     * Execute a SQL
     */
    abstract public function execute($sql=null);
    
    /**
     * Get all results array
     */
    abstract public function getAll($sql=null);
    /**
     * Get one row array
     */
    abstract public function getRow($sql=null);

    /**
     * Get the first column from first row
     */
    abstract public function getOne($sql=null);
    
    /**
     * Transaction begin
     */
    abstract public function begin();
    /**
     * Transaction commit
     */
    abstract public function commit();
    /**
     * Transaction rollback
     */
    abstract public function rollback();
    /**
     * Return last error message
     */
    abstract public function lastError();
    /**
     * Return line affected from last SQL
     */
    abstract public function lastAffected();
    /**
     * Return insert id from last insert
     */
    abstract public function lastInsertId();

}