<?php

/**
 * Pager Helper Class
 * 
 * @package Revship
 * @subpackage Library
 * @author Lijun Shen <lijunshen@revship.com>
 * @since 1.0.0
 */
class Revship_Pager_Helper
{
    /**
     * Current Page Number
     */
	public $curPageNum;
	/**
     * All Items Number
	 */
	public $totalItemNum;
	/** 
	 * Items Per Page
	 */
	public $itemNumPerPage;
	/** 
	 * Link Format
	 */
	public $linkFormat;
	/**
	 * Total Pages Number
	 */
	private $totalPagesNum;
	/**
	 * Previous Page Text
	 */
	public $prename="<";
	/**
	 * Head Page Text
	 */
	public $headname="|<";
	/**
	 * Next Page Text
	 */
	public $nextname=">";
	/**
	 * Last Page Text
	 */
	public $lastname=">|";
	
	/**
	 * Next Page Number
	 */
	private $nextPageNum;
	/**
	 * Previous Page Number
	 */
	private $prevPageNum;
	
	function __construct()
	{
		$this->curPageNum=1;
		$this->itemNumPerPage=20;
	}
	public function makeHTML()
	{ 
		if($this->totalItemNum==0) return false;
			//获取页数
			$this->totalPagesNum=ceil($this->totalItemNum/$this->itemNumPerPage);
			
			///////获取上下页页码////
			if($this->totalPagesNum>1)
			{
				if($this->curPageNum==1)
				$this->nextPageNum=$this->curPageNum+1;
				elseif($this->curPageNum==$this->totalPagesNum)
				$this->prevPageNum=$this->curPageNum-1;
				else
				{
				$this->prevPageNum=$this->curPageNum-1;
				$this->nextPageNum=$this->curPageNum+1;
				}
				}
		////////获取上下页输出////  $pre  $next
		if(!empty($this->prevPageNum))
			$pre="<a href=\"javascript:;\" onclick=\"get(".$this->fp.",".$this->prevPageNum.$this->additionValue.");return false;\">$this->prename</a>";
		else
			$pre="<span class=\"disabled\">$this->prename</span>";
			
			
		if(!empty($this->nextPageNum))
			$next="<a href=\"javascript:;\" onclick=\"get(".$this->fp.",".$this->nextPageNum.$this->additionValue.");return false;\">$this->nextname</a>";
		else
			$next="<span class=\"disabled\">$this->nextname</span>";
		
		//////////获取首页尾页输出 ////$head  //last
		if($this->curPageNum==1)
			$head="<span class=\"disabled\">$this->headname</span>";
		else
			$head="<a href=\"javascript:;\" onclick=\"get(".$this->fp.",1".$this->additionValue.");return false;\">$this->headname</a>";
		
		
		if($this->curPageNum==$this->totalPagesNum)
			$last="<span class=\"disabled\">$this->lastname</span>";
		else
			$last="<a href=\"javascript:;\" onclick=\"get(".$this->fp.",$this->totalPagesNum".$this->additionValue.");return false;\">$this->lastname</a>";
		
		
		/////////右省略
		if($this->totalPagesNum-$this->curPageNum>3&&$this->totalPagesNum>7)
		{
			$ellipsis_r="...";
		}
		/////////左省略
		if($this->totalPagesNum>7&&$this->curPageNum>4)
		{
			$ellipsis_l="...";
		}
		
		////////获取页码连接输出
		//一共7页
		if($this->totalPagesNum<=7)
		{
			$ifrom=1;
			$ito=$this->totalPagesNum;
		}
		else // more than 7 pages
		{
			//CurPage前4项   同样从1生成
			if($this->curPageNum<=4)
			{
				$ifrom=1;
				$ito=7;
			}
			//CupPage后4项   同样生成至最后页
			elseif($this->totalPagesNum-$this->curPageNum<=3)
			{
				$ifrom=$this->curPageNum-6+$this->totalPagesNum-$this->curPageNum;
				$ito=$this->totalPagesNum;
			}
			//CurPage中段
			else
			{
				$ifrom=$this->curPageNum-3;
				$ito=$this->curPageNum+3;
			}
		}
		////num输出
		for($i=$ifrom; $i<=$ito; $i++)
		{
			if($i==$this->curPageNum)
			{
				$num.="<span class=\"current\">$i</span>";
			}
			else
			{
				$num.="<a href=\"javascript:;\" onclick=\"get(".$this->fp.",$i".$this->additionValue.");return false;\">$i</a>";
			}
		}
		///////////////////////终极输出！！！
		
		$output="<div id=\"digg\">";
		$output.=$head;
		$output.=$pre;
		$output.=$ellipsis_l;
		$output.=$num;
		$output.=$ellipsis_r;
		$output.=$next;
		$output.=$last;
		$output.="</div>";
		
		return $output;
	}

}
