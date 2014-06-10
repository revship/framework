<?php
class Revship_Pager_Base
{
	public $pageSize = 32;
	public $currentPage = 0;
	public $recordCount = 0;
	public $pageTotal = 0;
	//最多1000条数据
	public $maxRecordCount;
	
	public function init($pageSize, $currentPage, $recordCount,$maxRecordCount = 1000)
	{	
		$this->pageSize = $pageSize;
		$this->currentPage = $currentPage;
		$this->recordCount = $recordCount;
		$this->maxRecordCount = $this->recordCount;
		//最多取1000条
		if($maxRecordCount){
            $this->maxRecordCount = min($this->recordCount,$maxRecordCount);
		}
		// 总页数
		$this->pageTotal = (int)ceil($this->maxRecordCount / $this->pageSize);
		
		$this->currentPage = min($this->currentPage,$this->pageTotal);
	}
	
	/**
	 * 判断当前是否是第一页
	 *
	 */
	public function isFirstPage()
	{
		return $this->currentPage==1;
	}
	
	/**
	 * 是否多于一页
	 *
	 */
	public function hasMoreThanOnePage()
	{
		return $this->recordCount > $this->pageSize;
		
	}
	
	public function getCurrentPage()
	{
		return $this->currentPage;
	}
	
	public function getNextPage()
	{
		if ( $this->pageTotal == $this->currentPage )
			return $this->currentPage;
		return $this->currentPage + 1;
	}
	
	public function getPreviousPage()
	{
		if ( $this->currentPage == 1 )
			return $this->currentPage;
		return $this->currentPage - 1;
	}
	
	public function getPageTotal()
	{
		return $this->pageTotal;
	}
	
	/**
	 * 判断是否还有上一页
	 * @return boolean
	 */
	public function hasPreviousPage()
	{
		return $this->currentPage > 1;
	} 
	
	/**
	 * 判断是否还有下一页
	 * @return boolean
	 */
	public function hasNextPage()
	{
		return $this->currentPage < $this->pageTotal;
	}

	/**
	 * 返回总条目数
	 * @return int
	 */
	public function getRecordCount() 
	{
		return $this->recordCount;
	}	
}
