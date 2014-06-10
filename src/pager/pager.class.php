<?php
/**
 * Pager Class
 * 
 * @package Revship
 * @subpackage Library
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
Revship::getLibClass('revship.pager.base');
class Revship_Pager extends Revship_Pager_Base {

	/**
	 * 查询sql语句
	 *
	 * @var string
	 */
	public $sql;
	
	/**
	 * 查询总数的sql语句
	 *
	 * @var string
	 */
	public $countSql;
	
	/**
	 * BaseModel
	 *
	 * @var object
	 */
	public $model;
	
	
	public $data = array();
	/**
	 * 
	 * 
	 * @param int $pageSize       每页显示条数
	 * @param string $countSql  数据总条数
	 * @param string $sql       数据查询语句
	 * @param string $pageTag   url页码参数名
	 */
	function getPager($pageSize, $sql = null, $model, $countSql = '' ,$pageTag='page')
	{
		$page = Revship_Http::getREQUEST($pageTag, 1);
		if($sql)
		{
		    $this->sql = $sql;
		}
		else 
		{
		    $this->sql = $model->makeSql();
		}
		$this->countSql = $countSql;
		$this->model = $model;
		$total = $this->getTotalNum();
		parent::init($pageSize, $page, $total);
		return $this;
	}
	
	/**
	 * 拼分页sql
	 *
	 * @return string
	 */
	public function limitedSql()
	{
		if( $this->recordCount > 0 )
		{
			return sprintf('LIMIT %d,%d', ($this->currentPage - 1) * $this->pageSize, $this->pageSize);
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * 查询数据列表
	 *  
	 * @return array        结果数组
	 */
	public function getDataList()
	{
		$limitStr = $this->limitedSql();
		return $this->model->getAll($this->sql . " $limitStr ");
	}
	
	/**
	 * 获取数据总条数
	 *
	 * @return int
	 */
	public function getTotalNum()
	{
		//对负责sql查询，单独查询count
		if( !$this->countSql )
		{
			$pattern = "/^select\s+(.*)\s+from/ism";
			//处理select里面带嵌套子查询的问题
			$this->countSql = preg_replace($pattern, "select count(*) from", $this->sql);
		}
		return $this->model->getOne($this->countSql);
	}
	
	/**
	 * 拼查询url
	 *
	 * @return string
	 */
	public function buildQueryStr()
	{
		$buf = array();
		foreach ( $_GET as $k => $v)
		{
			if ( $k != 'page' )
			{
				$buf[] = $k . '=' . urlencode($v);
			}
		}
		$rt = '?';
		if ( $buf )
		{
			$rt .= implode( '&', $buf );
		}
		return $rt;
	}

    
	/**
     * Get offset for given page (fix page number if needed)
     *
     * @param int $page      page number
     * @param int $pageSize  page size (rows per page)
     * @param int $allItems  records number
     * @return int offset of LIMIT in SQL
     */
	public function getOffset($page, $pageSize, $allItems)
	{
		if ($pageSize)
        {
            $pages  = ceil($allItems / $pageSize);
            $page   = max(1, min($pages, $page));
            return $pageSize*($page-1);
        }
        return 0;
	}
}

?>