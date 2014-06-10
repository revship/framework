<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Model
{
    protected $_model = null;
    protected $_user = null;
    /**
     * Table Name
     * @var string
     */
    protected $dbTable = null;
    /**
     * Attributes which DB table schema have
     * @var array
     */
    protected $attributesArray;
    public $pageSize = 15;
    
    public function __construct()
    {
        $this->_model = Revship::db();
    }
    protected function _user( $field = false )
    {
        $this->_user = Revship::lib('auth')->getUser();
        if($field)
        {
            return $this->_user[$field];
        }
        return $this->_user;
    }
	/**
     * Get the table name with prefix
     */
    public function getDbTableName( $needPrefix = true )
    {
        if($needPrefix)
        {
            $dbTableName = $this->_model->getTablePrefixed($this->dbTable);
        }
        else
        {
            $dbTableName = $this->dbTable;
        }
    	    return $dbTableName;
    }
    public function getPager($sql = null)
    {
        return Revship::lib('pager')->getPager($this->pageSize,$sql,$this->_model);
    }
    public function getPagerItems()
    {
        return self::getPager()->getDataList();
    }
    public function getLastInsertId()
    {
        return $this->_model->lastInsertId();
    }
    /**
     * Convert form values from object to array
     * @param object $formObject
     * @param string $attributes
     */
    protected function formObjectToArray(&$formObject,$attributes)
    {
        $this->attributesToArray($attributes);
        $schemeArray = array();
        foreach($this->attributesArray as $key)
        {
            $schemeArray[$key] = $formObject->$key;
        }
        return $schemeArray;
    }
    
    protected function dbArrayFilter($dbSchemaArray, &$array)
    {
        //Not Array? Convert to array
        if(!is_array($dbSchemaArray))
        {
            $dbSchemaArray = $this->attributesToArray($dbSchemaArray);
        }
        foreach($dbSchemaArray as $colName)
        {
            if(array_key_exists($colName,$array))
            {
                $newArray[$colName] = $array[$colName];
            }
        }
        /*
        if(!isset($newArray))
        {
            throw new Revship_Exception(501,'DB updating array is empty');
        }
        */
        $array=$newArray;
    } 
    /**
     * $this->attributeArray
     * convert  any,any,any  to  array('any','any','any')
     * @param unknown_type $attributes
     */
    protected function attributesToArray($attributes)
    {
        if(is_array($attributes))
        {
            $this->attributesArray = $attributes;
            return;
        }
        $attributesArray = explode(',',$attributes);
        foreach ($attributesArray as $attribute)
        {
             $attributesArray[] = trim($attribute);
        }
        $this->attributesArray = $attributesArray;
    }
    
    /**
     * Get all input names (without formName)
     * =>In order to assign values from Form Model to DB Model
     */
    protected function getNameArrayFromPost($formName)
    {
        $form = Revship::lib('http')->getPOST($formName);
        return array_keys($form);
    }
    /**
     * Create record for db from an form object
     * @param object $formObj
     * @param string $formName
     * @param array $forceParam  e.g. array('timestamp'=>12345)
     */
    protected function createFromFormObj(&$formObject,$formName, $forceParam=array())
    {
        if ( ! is_object($formObject) )
        {
            return false;
        }
        $importArray = $this->getNameArrayFromPost($formName);
        //Convert form object to db schema field array;
        $fields = $this->formObjectToArray($formObject, $importArray);
        if(!empty($forceParam))
        {
            $fields = array_merge((array) $fields, (array)$forceParam);
        }
        $this->dbArrayFilter($this->dbAllowFields, $fields);
        //Insert to db
        return $this->insertDb($fields);
    }
    /**
     * Modify record for db from an form object
     * @param object $formObj
     * @param string $formName
     * @param int $id
     * @param array $forceParam  e.g. array('timestamp'=>12345)
     */
    protected function editFromFormObj(&$formObject, $formName, $id, $forceParam=array(), $idField=null)
    {
        if ( ! is_object($formObject) )
        {
            return false;
        }
        $importArray = $this->getNameArrayFromPost($formName);
        //Convert form object to db schema field array;
        $fields = $this->formObjectToArray($formObject, $importArray);
        if(!empty($forceParam))
        {
            $fields = array_merge((array) $fields, (array)$forceParam);
        }
        $this->dbArrayFilter($this->dbAllowFields, $fields);
        return $this->updateDb($id, $fields, $idField);
    }
    /**
     * Change page size
     */
    public function setPageSize($pageSize)
    {
        if(is_numeric($pageSize))
        {
            $this->pageSize = $pageSize;
        }
        return $this;  
    } 
    /**
     * Update the db. (where $idField = $id)
     * @param int $id
     * @param string $fields
     * @param string $idField
     */
    public function updateDb($id, $fields, $idField=null)
    {
        if(!$idField)
        {
            $idField = $this->dbAllowFields[0];
        }
        if(is_array($fields))
        {
            $this->dbArrayFilter($this->dbAllowFields,$fields);
        }
        return Revship::db()->update($this->dbTable, $fields, "{$idField} = '{$id}'");
    }
    /**
     * @param $fields array( 'id'=> 5, 'name'=> 7) 
     */
    public function insertDb(array $fields)
    {
        // Filter array to be allowed db schema
        $this->dbArrayFilter($this->dbAllowFields,$fields);
        // Insert to db
        return Revship::db()->insert($this->dbTable,$fields);
    }
    /**
     * @param $query  a query or an id
     * eg. 5, 'category_id' 
     * OR  5, null    stands for PK=5
     * OR  'category_id=5' , null
     */
    public function deleteDb( $query, $field = null)
    {
        // Delete from db
        if (! $field) 
        {
            $field = $this->dbAllowFields[0];
        }
        if(is_numeric($query))
        {
            $query = $field .'='.$query;
        } 
        else if(strstr($query, '=')===false)
        {
            $query = $field .'=\''.$query.'\'';
        }
        return Revship::db()->delete($this->dbTable,$query);
    }
    public function count($condAnd)
    {
        return $this->_model
                 ->select('count(*)')
                 ->from($this->dbTable)
                 ->whereAnd($condAnd)
                 ->getOne();
    }
    public function sum($field, $condAnd)
    {
        return $this->_model
                 ->select('SUM('.$field.')')
                 ->from($this->dbTable)
                 ->whereAnd($condAnd)
                 ->getOne();
    }
    protected function purifyString($string)
    {
        return Revship::db()->escape(trim($string));
    }
    public function genEmptyRecordArray()
    {
        $record = array();
        foreach($this->dbAllowFields as $key)
        {
            $record[$key] = '';
        } 
        return $record;
    }

}
