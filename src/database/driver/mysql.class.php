<?PHP

Revship::getLibClass('revship.database.abstract');

class Revship_Database_Driver_Mysql extends Revship_Database_Abstract
{
    private $connected  = false;
    private $connection = null;

    private $host       = null;
    private $userName   = null;
    private $passwd     = null;
    private $database   = null;
    private $port       = null;
    private $encoding   = null;
    private $table_prefix = '';
    protected $_query = array();
    
    public function __construct($dbConfig)
    {
        $this->connect($dbConfig);
        return $this;
    }
    public function isConnected()
    {
        return $this->connected;
    }
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    /*
    protected function usePreferedServer($dbConfig)
    {
        if(!array_key_exists('prefered_server_name',$dbConfig))
            return false;
        
        $this->host     = $dbConfig[$dbConfig['prefered_server_name'].'.host'];
        $this->userName = $dbConfig[$dbConfig['prefered_server_name'].'.user'];
        $this->passwd   = $dbConfig[$dbConfig['prefered_server_name'].'.password'];
        $this->database = $dbConfig[$dbConfig['prefered_server_name'].'.dbname'];
        $this->port     = $dbConfig[$dbConfig['prefered_server_name'].'.port'] ? $dbConfig[$dbConfig['prefered_server_name'].'.port'] : 3306;
        $this->encoding = $dbConfig[$dbConfig['prefered_server_name'].'.encoding'] ? $dbConfig[$dbConfig['prefered_server_name'].'.encoding'] : 'utf8';
        return true;
    }    
    protected function useSecondaryServer($dbConfig)
    {
        if(!array_key_exists('secondary_server_name',$dbConfig))
            return false;
            
        $this->host     = $dbConfig[$dbConfig['secondary_server_name'].'.host'];
        $this->userName = $dbConfig[$dbConfig['secondary_server_name'].'.user'];
        $this->passwd   = $dbConfig[$dbConfig['secondary_server_name'].'.password'];
        $this->database = $dbConfig[$dbConfig['secondary_server_name'].'.dbname'];
        $this->port     = $dbConfig[$dbConfig['secondary_server_name'].'.port'] ? $dbConfig[$dbConfig['secondary_server_name'].'.port'] : 3306;
        $this->encoding = $dbConfig[$dbConfig['secondary_server_name'].'.encoding'] ? $dbConfig[$dbConfig['secondary_server_name'].'.encoding'] : 'utf8';
        return true;        
    }
    */
    public function connect($dbConfig = null)
    {
        if($dbConfig)
        {
            $this->host     = $dbConfig['host'];
            $this->userName = $dbConfig['user'];
            $this->passwd   = $dbConfig['password'];
            $this->database = $dbConfig['dbname'];
            $this->port     = $dbConfig['port'] ? $dbConfig['port'] : 3306;
            $this->encoding = $dbConfig['encoding'] ? $dbConfig['encoding'] : 'utf8';
            $this->table_prefix = $dbConfig['table_prefix'];
        }
        
        if(!$this->connected) 
        {
            $time_start = $this->microtime_float();
            // Connect
            $this->connection = mysql_connect($this->host.':'.$this->port, $this->userName, $this->passwd);
            
            if(false === $this->connection) 
            {
                for($i=0; $i<3; $i++) 
                {
                    $this->connection = mysql_connect($this->host.':'.$this->port, $this->userName, $this->passwd);
                    if($this->connection) {
                        break;
                    }
                }   
                //@todo Failed to connect db
                throw new Exception('Failed to connect database. Please check configuration or enhance database servers. ' . mysql_error());
                return false;
            }
            $db_selected = mysql_select_db($this->database, $this->connection);
            if (!$db_selected) {
                throw new Exception('Failed to select database [ '.$this->database.' ]: ' . mysql_error());
            }
            $time_end = $this->microtime_float();
            $conn_time = $time_end - $time_start;
        }
        else
        {
            return $this->connected;
        }

        if(false !== $this->connection)
        {
            $this->connected = true;
            mysql_query("set names ".$this->encoding);
        }
        else
        {
            throw new Exception($this->lastError());
        }
        return $this->connected;
    }
    
    public function disconnect()
    {        
        if($this->connection)
        {
            mysql_close($this->connection);
            $this->connected = false;
        }
    }
    
    public function getTablePrefixed($tableName = null)
    {
        return $this->table_prefix.$tableName;
    }
    public function getResultResource($sql)
    {
        $this->connect();
        return mysql_query($sql,$this->connection);
    }
    public function getAll($sql=null)
    {
        $this->connect();

        if(!$sql)
        {
            $sql=$this->makeSql();
        }
        
        if ($result = mysql_query($sql,$this->connection))
        {    
            $res = array();
            while($row = mysql_fetch_assoc($result))
            {
                $res[] = $row;
            }
            mysql_free_result($result);
            return $res;
        } else {
            Revship::log(__METHOD__.'-'."SQL Error: {$sql}",'ERROR');
            throw new Revship_Exception(501,'Database Query Error (getAll) ['.$sql.']: '.$this->lastError());
        }
    }
    
    
    public function getRow($sql=null)
    {
        $this->connect();

        if(!$sql)
        {
            $sql=$this->makeSql();
        }
        
        if($result = mysql_query($sql,$this->connection))
        {
            $res = mysql_fetch_assoc($result);
            mysql_free_result($result);
            return $res;
        } else {
            Revship::log(__METHOD__.'-'."SQL Error: {$sql}",'ERROR');
            throw new Revship_Exception(501,'Database Query Error (getRow):['.$sql.'] '.$this->lastError());
        }
    }

    public function getOne($sql=null)
    {
        $this->connect();
        
        if(!$sql)
        {
            $sql=$this->makeSql();
        }

        if($result = mysql_query($sql,$this->connection))
        {
            $res = mysql_fetch_row($result);
            mysql_free_result($result);
            return $res[0];
        } else {
            Revship::log(__METHOD__.'-'."SQL Error: {$sql}",'ERROR');
            throw new Revship_Exception(501,'Database Query Error (getOne) ['.$sql.']: '.$this->lastError());
        }
    }
    
    public function begin()
    {
        if($this->execute('START TRANSACTION'))
            return true;
        else
            return false;
        
    }
    
    public function commit()
    {
        $this->connect();
        if($this->connection->commit())
            return true;
        else
            return false;
    }
    
    public function rollback()
    {
        $this->connect();
        if($this->connection->rollback())
            return true;
        else
            return false;
    }

    public function lastError()
    {
        if(mysql_errno($this->connection))
        {
            return mysql_errno($this->connection);
        }
        else
        {
            return null;
        }
    }

    public function lastAffected()
    {
        $this->connect();
        return mysql_affected_rows($this->connection);
    }

    public function lastInsertId()
    {
        $id = $this->getRow('SELECT LAST_INSERT_ID() AS insertID');
        if($id !== false && !empty($id) && isset($id['insertID']))
        {
            return $id['insertID'];
        }
        else
        {
            return null;
        }
    }
    
    
    public function __destruct()
    {
        if($this->connected)
        {
            unset($this->connected);
        }
        if($this->connection)
        {
            unset($this->connection);
        }
    }
    
    public function autoInsert($table_name,&$values)
    {
        
        $f=array();
        $v=array();
        foreach($values as $key=>$value)
        {
            $f[]=$key;
            if(is_array($value))
            {
                $raw_string = $value['raw'];
                $v[]="$raw_string";
            }
            else
            {
                $v[]="'$value'";
            }
        }
        $field_sql=implode(',',$f);
        $value_sql = implode(',',$v);
        $sql="insert into ".$this->table_prefix.$table_name." ( $field_sql ) values ( $value_sql )";
        return $this->execute($sql);
    }
    
    private function _log($msg)
    {
        
    }
    
    public function escape($param)
    {
        if (is_array($param))
        {
            return array_map(array(&$this, 'escape'), $param);
        }

        if (get_magic_quotes_gpc())
        {
            $param = stripslashes($param);
        }

        $param = @mysql_real_escape_string($param);

        return $param;
    }   
    public function select($sSelect, $unsetAll = true)
    {
        if($unsetAll)
        {
                $this->unsetAll();
        }
        $this->_query['select'] = 'SELECT '.$sSelect;
        return $this;
    }
    public function unsetAll()
    {
            unset($this->_query['select']);
                unset($this->_query['from']);
                unset($this->_query['where']);
                unset($this->_query['order']);
                unset($this->_query['having']);
                unset($this->_query['limit']);
                unset($this->_query['group']);
                unset($this->_query['join']);
    }
    public function where($aConds)
    {
        $this->_query['where'] = '';
        if (is_array($aConds) && count($aConds))
        {
            foreach ($aConds as $sValue)
            {
                if( ! is_array($sValue) )
                    $this->_query['where'] .= $sValue . ' ';
                else
                    $this->_query['where'] .= $sValue[0]. '\''. $sValue[1] . '\' ';
            }
            $this->_query['where'] = "WHERE " . trim(preg_replace("/^(AND|OR) (.*?)/i", "", trim($this->_query['where'])));
        }
        else 
        {
            if (!empty($aConds))
            {
                $this->_query['where'] .= 'WHERE ' . $aConds;    
            }
        }
        
        return $this;
    }
    
    public function whereAND($aConds)
    {
        $noAnd = false;
        if(empty($this->_query['where'])  
            && ( (is_array($aConds) && count($aConds)) 
                ||(!empty($aConds)) )
            )
        {
             $this->_query['where'] = ' WHERE ';
             $noAnd = true;
        }
        if (is_array($aConds) && count($aConds))
        {
            foreach ($aConds as $sValue)
            {
                if( ! is_array($sValue) )
                {
                    $this->_query['where'] .= ($noAnd == false? ' AND ':' ') . trim(preg_replace("/^(AND|OR) (.*?)/i", "", trim($sValue)));
                }
                else 
                {
                    $this->_query['where'] .= ($noAnd == false? ' AND ':' ') . trim(preg_replace("/^(AND|OR) (.*?)/i", "", trim($sValue[0]. '\''. $sValue[1] . '\'')));
                }
                $noAnd = false;
            }
        }
        else if (!empty($aConds))
        {
            $this->_query['where'] .= ($noAnd == false? ' AND ':' ') . $aConds;
        }
        return $this;
    }
    
    public function whereOR($aConds)
    {
        $noOR = false;
        if(empty($this->_query['where'])
            && ( (is_array($aConds) && count($aConds)) 
                ||(!empty($aConds)) )
            )
        {
             $this->_query['where'] = ' WHERE ';
             $noOR = true;
        }
        if (is_array($aConds) && count($aConds))
        {
            foreach ($aConds as $sValue)
            {
                if( ! is_array($sValue) )
                {
                    $this->_query['where'] .= ($noOR == false? ' OR ':' ') . trim(preg_replace("/^(AND|OR)(.*?)/i", "", trim($sValue)));
                }
                else 
                {
                    $this->_query['where'] .= ($noOR == false? ' OR ':' ') . trim(preg_replace("/^(AND|OR)(.*?)/i", "", trim($sValue[0]. '\''. $sValue[1] . '\'')));
                }
                $noOR = false;
            }
        }
        else if (!empty($aConds))
        {
            $this->_query['where'] .= ($noOR == false? ' OR ':' ') . $aConds;
        }
        return $this;
    }
    
    public function from($sTable, $sAlias = '')
    {
        $this->_query['table'] = 'FROM ' . $this->table_prefix .$sTable . ($sAlias ? ' AS ' . $sAlias : '');        
        
        return $this;
    }
    
    public function orderBy($sOrder)
    {
        if (!empty($sOrder))
        {        
            $this->_query['order'] = 'ORDER BY ' . $sOrder;
        }
        
        return $this;
    }
    
    public function groupBy($sGroup)
    {
        $this->_query['group'] = 'GROUP BY ' . $sGroup;
        
        return $this;
    }

    public function having($sHaving)
    {
        $this->_query['having'] = 'HAVING ' . $sHaving;
        
        return $this;
    }
    
    public function leftJoin($sTable, $sAlias, $mParam = null)
    {
        $this->_join('LEFT JOIN', $this->table_prefix . $sTable, $sAlias, $mParam);
        
        return $this;
    }
    
    public function innerJoin($table, $sAlias, $mParam = null)
    {
        $this->_join('INNER JOIN', $this->table_prefix . $table, $sAlias, $mParam);
        
        return $this;
    }    
    
    public function join($table, $sAlias, $mParam = null)
    {
        $this->_join('JOIN', $this->table_prefix . $table, $sAlias, $mParam);
        
        return $this;
    }    
    
    public function limit($page, $limit = null, $cnt = null)
    {
        if ($limit === null && $cnt === null && $page !== null)
        {
            $this->_query['limit'] = 'LIMIT ' . $page;    
            
            return $this;
        }
        
        $offset = Revship::lib('pager')->getOffset($page, $limit, $this->countRows());
        
        $this->_query['limit'] = ($limit ? 'LIMIT ' . $limit : '') . ($offset ? ' OFFSET ' . $offset : '');
        
        return $this;
    }
    
    public function countRows()
    {
        $oldSelect = $this->_query['select'];
        $this->_query['select'] = 'SELECT count(*) ';
        $rowNum = $this->getOne();
        $this->_query['select'] = $oldSelect;
        return $rowNum;
    }
    
    public function execute($sql=null)
    {
        if(!$sql)
        {
            $sql=$this->makeSql();
        }        
        $this->connect();
        if (mysql_query($sql,$this->connection)) 
        {
            Revship::log(__METHOD__.'-'.'SQL Trace: '.$sql);
            return mysql_affected_rows($this->connection);
        }
        else
        { 
            Revship::log(__METHOD__."-SQL Error: {$sql}",'ERROR');
            throw new Revship_Exception(501,"Execute Error [{$sql}]".$this->lastError());
        }
        
    }
    
    public function clean()
    {
        $this->_query = array();        
    }
    public function replace($table, $valueArray = array(), $escape = true, $returnQuery = false)
    {
        return $this->insert($table, $valueArray , $escape , $returnQuery , true);
    }
    /**
     * Performs insert of one row. Accepts values to insert as an array:
     *    'column1' => 'value1'
     *    'column2' => 'value2'
     * 
     * @access    public
     * @param string  $table    table name
     * @param array   $aValues   column and values to insert
     * @param boolean $bEscape true - method escapes values (with "), false - not escapes
     * @return int last ID (or 0 on error)
     */
    public function insert($table, $valueArray = array(), $escape = true, $returnQuery = false, $isReplace = false)
    {
            $valueString = '';
            foreach ($valueArray as $v)
            {
                if (is_null($v))
                {
                    $valueString .= "NULL, ";
                }
                else 
                {
                    $valueString .= "'" . ($escape ? $this->escape($v) : $v) . "', ";
                }
            }
            $valueString = rtrim(trim($valueString), ',');
            

        $sql = $this->_insert($table, implode(', ', array_keys($valueArray)), $valueString, $isReplace);
 
        if ($this->execute($sql))
        {
                if ($returnQuery)
                {
                    return $sql;
                }

            return $this->lastInsertId();
        }

        return 0;
    }    
    
    public function multiInsert($table, $fields, $values)
    {
        $sSql = "INSERT INTO " . $this->table_prefix .$table. " (" . implode(', ', array_values($fields)) . ") ";
        $sSql .= " VALUES\n";
        foreach ($values as $aValue)
        {
            $sSql .= "\n(";
            foreach ($aValue as $mValue)
            {
                if (is_null($mValue))
                {
                    $sSql .= "NULL, ";
                }
                else 
                {
                    $sSql .= "'" . $this->escape($mValue) . "', ";
                }
            }
            $sSql = rtrim(trim($sSql), ',');
            $sSql .= "),";
        }
        $sSql = rtrim($sSql, ',');  
        
        if ( $this->execute($sSql))
        {
            return $this->lastInsertId();
        }
        
        return 0;
    }
    
    /**
     * Performs update of rows.
     * 
     * @access public
     * @param string $table  table name
     * @param array  $values array of column=>new_value
     * @param string $sCond   condition (without WHERE)
     * @param boolean $bEscape true - method escapes values (with "), false - not escapes
     * @return boolean true - update successfule, false - error
     */
    public function update($table, $values = array(), $cond = null, $bEscape = true)
    {
        if(!is_array($values))
        {
            $sets = $values;
        }
        else
        {
            $sets = '';
            foreach ($values as $sCol => $sValue)
            {
                $sCmd = "=";
                if (is_array($sValue))
                {
                    $sCmd = $sValue[0];
                    $sValue = $sValue[1];    
                }
                
                // $sets .= "{$sCol} {$sCmd} " . (is_null($sValue) ? 'NULL' : (is_numeric($sValue) ? $sValue : ($bEscape ? "'" . $this->escape($sValue) . "'" : $sValue))) . ", ";
                $sets .= "{$sCol} {$sCmd} " . (is_null($sValue) ? 'NULL' : ($bEscape ? "'" . $this->escape($sValue) . "'" : $sValue)) . ", ";
            }
            $sets[strlen($sets)-2] = '  ';        
        }
        return $this->execute($this->_update($table, $sets, $cond));
    } 
    
    /**
     * Delete entry from the database
     * 
     * @access public
     * @param string $table is the table name
     * @param string $sQuery is the query we will run
     */
    public function delete($table, $query, $limit = null)
    {
        if ($limit !== null)
        {
            $query .= ' LIMIT ' . (int) $limit;
        }
        
        return $this->execute("DELETE FROM " . $this->table_prefix .$table ." WHERE ". $query);        
    }   
    
    public function dropTables($tables, $aVals = array())
    {
        foreach ($tables as $table)
        {
            $this->execute("DROP TABLE " . $this->table_prefix .$table);        
        }            
    }
    
    protected function _join($sType, $table, $sAlias, $mParam = null)
    {
        
        if (!isset($this->_query['join']))
        {
            $this->_query['join'] = '';
        }
        $this->_query['join'] .= $sType . " " . $table . " AS " . $sAlias;
        if (is_array($mParam))
        {
            $this->_query['join'] .= "\n\tON(";
            foreach ($mParam as $sValue)
            {
                $this->_query['join'] .= $sValue . " ";
            }
        }
        else 
        {
            if (preg_match("/(AND|OR|=)/", $mParam))
            {
                $this->_query['join'] .= "\n\tON({$mParam}";
            }
            else 
            {
                // Not supported with other drivers so we don't use this anymore
                new Revship_Exception(502,'Not allowed to use "USING()" in SQL queries any longer.', E_USER_ERROR);
            }
        }
        $this->_query['join'] = preg_replace("/^(AND|OR)(.*?)/i", "", trim($this->_query['join'])) . ")\n";
    } 
    
    protected function _insert($table, $sFields, $sValues, $isReplace = false)
    {
        $op = 'INSERT';
        if($isReplace)
        { 
            $op = 'REPLACE';
        }
        return $op. ' INTO '  . $this->table_prefix . $table . ' '.
            '        (' . $sFields . ')'.
            ' VALUES (' . $sValues . ')';            
    }
    
    protected function _update($table, $sets, $cond)
    {
        return 'UPDATE ' . $this->table_prefix . $table . ' SET ' . $sets . ' WHERE ' . $cond;
    }
    
    public function makeSql()
    {
        $sql = $this->_query['select'] . "\n";
        $sql .= $this->_query['table'] . "\n";
        $sql .= (isset($this->_query['join']) ? $this->_query['join'] . "\n" : '');
        $sql .= (isset($this->_query['where']) ? $this->_query['where'] . "\n" : '');
        $sql .= (isset($this->_query['group']) ? $this->_query['group'] . "\n" : '');
        $sql .= (isset($this->_query['having']) ? $this->_query['having'] . "\n" : '');
        $sql .= (isset($this->_query['order']) ? $this->_query['order'] . "\n" : '');
        $sql .= (isset($this->_query['limit']) ? $this->_query['limit'] . "\n" : '');
        return $sql;
    }
}
