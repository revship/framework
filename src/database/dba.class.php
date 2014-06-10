<?php

class Revship_Database_Dba
{
	protected $_query = array();
	
	public function __construct()
	{
	}
	
	public function select($sSelect)
	{
		if (!isset($this->_query['select']))
		{
			$this->_query['select'] = 'SELECT ';
		}
		$this->_query['select'] .= $sSelect;	
		return $this;
	}
	
	public function where($aConds)
	{
		$this->_query['where'] = '';
		if (is_array($aConds) && count($aConds))
		{
			foreach ($aConds as $sValue)
			{
				$this->_query['where'] .= $sValue . ' ';
			}
			$this->_query['where'] = "WHERE " . trim(preg_replace("/^(AND|OR)(.*?)/i", "", trim($this->_query['where'])));
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
	
	public function from($sTable, $sAlias = '')
	{
		$this->_query['table'] = 'FROM ' . $sTable . ($sAlias ? ' AS ' . $sAlias : '');		
		
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
		$this->_join('LEFT JOIN', $sTable, $sAlias, $mParam);
		
		return $this;
	}
	
	public function innerJoin($table, $sAlias, $mParam = null)
	{
		$this->_join('INNER JOIN', $table, $sAlias, $mParam);
		
		return $this;
	}	
	
	public function join($table, $sAlias, $mParam = null)
	{
		$this->_join('JOIN', $table, $sAlias, $mParam);
		
		return $this;
	}	
	
	public function limit($iPage, $sLimit = null, $iCnt = null)
	{
		if ($sLimit === null && $iCnt === null && $iPage !== null)
		{
			$this->_query['limit'] = 'LIMIT ' . $iPage;	
			
			return $this;
		}
		
		$iOffset = ($iCnt === null ? $iPage : Phpfox::getLib('pager')->getOffset($iPage, $sLimit, $iCnt));
		
		$this->_query['limit'] = ($sLimit ? 'LIMIT ' . $sLimit : '') . ($iOffset ? ' OFFSET ' . $iOffset : '');
		
		return $this;
	}
	
	public function execute($sType = null, $aParams = array())
	{
		$sSql = $this->_query['select'] . "\n";
		$sSql .= $this->_query['table'] . "\n";
		$sSql .= (isset($this->_query['join']) ? $this->_query['join'] . "\n" : '');
		$sSql .= (isset($this->_query['where']) ? $this->_query['where'] . "\n" : '');
		$sSql .= (isset($this->_query['group']) ? $this->_query['group'] . "\n" : '');
		$sSql .= (isset($this->_query['having']) ? $this->_query['having'] . "\n" : '');
		$sSql .= (isset($this->_query['order']) ? $this->_query['order'] . "\n" : '');
		$sSql .= (isset($this->_query['limit']) ? $this->_query['limit'] . "\n" : '');
		$sSql .= '/* OO Query */';
	
		if (method_exists($this, '_execute'))
		{
			$sSql = $this->_execute();
		}
		
	}		
	
	public function clean()
	{
		$this->_query = array();		
	}
	
    /**
     * Performs insert of one row. Accepts values to insert as an array:
     *    'column1' => 'value1'
     *    'column2' => 'value2'
     * 
     * @access	public
     * @param string  $table    table name
     * @param array   $aValues   column and values to insert
     * @param boolean $bEscape true - method escapes values (with "), false - not escapes
     * @return int last ID (or 0 on error)
     */
    public function insert($table, $aValues = array(), $bEscape = true, $bReturnQuery = false)
    {    	
    	if (!$aValues)
    	{
    		$aValues = $this->_aData;
    	}
    	
    	$sValues = '';
    	foreach ($aValues as $mValue)
    	{
    		if (is_null($mValue))
    		{
    			$sValues .= "NULL, ";
    		}
    		else 
    		{
    			$sValues .= "'" . ($bEscape ? $this->escape($mValue) : $mValue) . "', ";
    		}
    	}
    	$sValues = rtrim(trim($sValues), ',');
    	
    	if ($this->_aData)
    	{
    		$this->_aData = array();
    	}

        $sSql = $this->_insert($table, implode(', ', array_keys($aValues)), $sValues);
 
        if ($hRes = $this->query($sSql))
        {
        	if ($bReturnQuery)
        	{
        		return $sSql;
        	}

            return $this->getLastId();
		}

        return 0;
    }    
    
    public function multiInsert($table, $aFields, $aValues)
    {
    	$sSql = "INSERT INTO {$table} (" . implode(', ', array_values($aFields)) . ") ";
    	$sSql .= " VALUES\n";
    	foreach ($aValues as $aValue)
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
    	
        if ($hRes = $this->query($sSql))
        {
            return $this->getLastId();
		}
    	
    	return 0;
    }
    
    /**
     * Performs update of rows.
     * 
     * @access public
     * @param string $table  table name
     * @param array  $aValues array of column=>new_value
     * @param string $sCond   condition (without WHERE)
     * @param boolean $bEscape true - method escapes values (with "), false - not escapes
     * @return boolean true - update successfule, false - error
     */
    public function update($table, $aValues = array(), $sCond = null, $bEscape = true)
    {
        if (!is_array($aValues) && count($this->_aData))
        {
            $sCond = $aValues;
        	$aValues = $this->_aData;            
        	$this->_aData = array();
		}

        $sSets = '';
        foreach ($aValues as $sCol => $sValue)
        {
            $sCmd = "=";
            if (is_array($sValue))
            {
            	$sCmd = $sValue[0];
            	$sValue = $sValue[1];    
            }
        	
        	// $sSets .= "{$sCol} {$sCmd} " . (is_null($sValue) ? 'NULL' : (is_numeric($sValue) ? $sValue : ($bEscape ? "'" . $this->escape($sValue) . "'" : $sValue))) . ", ";
        	$sSets .= "{$sCol} {$sCmd} " . (is_null($sValue) ? 'NULL' : ($bEscape ? "'" . $this->escape($sValue) . "'" : $sValue)) . ", ";
        }
        $sSets[strlen($sSets)-2] = '  ';        
		
        return $this->query($this->_update($table, $sSets, $sCond));
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
    	
    	return $this->query("DELETE FROM {$table} WHERE ". $query);    	
    }   
    
	public function dropTables($aDrops, $aVals = array())
	{
		foreach ($aDrops as $sDrop)
		{
			$this->query("DROP TABLE {$sDrop}");		
		}			
	}
    
	protected function _join($sType, $table, $sAlias, $mParam = null)
	{
		if (PHPFOX_DEBUG && in_array(strtoupper($sAlias), $this->_aWords))
		{
			new Revship_Exception(502,'The alias "' . $sAlias . '" is a reserved SQL word. Use another alias to resolve this problem.', E_USER_ERROR);
		}		
		
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
	
	protected function _insert($table, $sFields, $sValues)
	{
		return 'INSERT INTO ' . $table . ' '.
        	'        (' . $sFields . ')'.
            ' VALUES (' . $sValues . ')';            
	}
	
	protected function _update($table, $sets, $cond)
	{
		return 'UPDATE ' . $table . ' SET ' . $sets . ' WHERE ' . $cond;
	}

}

?>