<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Controller
{
    protected $_tpl;
    protected $pageTitle = null;
    protected $pageTitleSeparator = ' - ';
    protected $pageTitleSuffix;
    protected $_user = null;
    
    /*
     * For Custom. 
     * @see self::pushCustomVar( key, val);
     */
    protected $customTplVars = array();
    public function __construct()
    {
        $this->_user = Revship::lib('auth')->getUser();
        $this->_tpl = Revship::lib('template');
        $this->_tpl->assign(array(
                'config'=>Revship::lib('config')->returnAllConfigArray(),
                'user'=>$this->_user,
                'ini'=>Revship_Http::getGET(),
                'pageTitle'=>Revship::lib('config')->getItem('site.title'),
        ));
    }
    /**
     * For custom.
     * 
     * @example Save value from php by $this->pushCustomVar('abc', 123)  
     * @example Template Usage:  {{$custom.abc}}   =>  123
     * @param string $key
     * @param mixed $value
     */
    protected function pushCustomVar( $key, $value )
    {
        $this->customTplVars[$key] = $value;
        $this->_tpl->assign('custom', $this->customTplVars);
    } 
    protected function purifyString($string)
    {
        return Revship::db()->escape(trim($string));
    }
    protected function setPageTitle($pageTitle, $pageTitleSuffix = null)
    {
        $this->pageTitle = $pageTitle;
        if(!$pageTitleSuffix)
        {
            $this->pageTitleSuffix = Revship::lib('config')->getItem('site.titleSuffix');
        }
        else
        {
            $this->pageTitleSuffix = $pageTitleSuffix;
        }
        $this->_tpl->assign('pageTitle',$this->pageTitle . $this->pageTitleSeparator . $this->pageTitleSuffix);
    }
    protected function setBreadcrumbs($parentTree,$lastChildFromPageTitle = true,$removeLastLink=false)
    {
        $tree = array();
        if(is_array($parentTree))
        {
            $tree=$parentTree;
        }
        if($lastChildFromPageTitle)
        {
            $tree[]=$this->pageTitle;
        }
        if($removeLastLink)
        {
            // remove the link of last child
            $originalTree = $tree;
            $lastLink = array_pop($tree);
            $lastName = array_keys($originalTree, $lastLink);
            $tree[] = $lastName[0];
        }
        $this->_tpl->assign('breadcrumbs',$tree);
    }
    /**
     * Auto get siteId and try to get site config(db),
     * and if site config is not found, it will get global config(file)
     * @example Call in a controller: $this->smartConfGet('site.title')   
     */
    protected function smartConfGet($confKey)
    {
        $siteId = Revship::ext("siteRouter")->getSiteId();
        // Get site conf
        $result = Revship::ext('config', $siteId)->getItem($confKey);
        // Site conf not exist, get global conf
        if(empty($result))
        {
            $result = Revship::conf()->getItem($confKey);
        }
        return $result;
    }
    /**
     * Get smart config, and unserialze value into array.
     */
    protected function smartConfGetUnserialize($confKey)
    {
        $confResultArr = $this->smartConfGet($confKey);
        $str = isset($confResultArr[0]) ? $confResultArr[0] : '';
        return unserialize($str);
    }
    
    /*
     * get siteId and try to set site config(db),
     * @example Call in a controller: $this->siteConfSetSingleItem('site.title', $value)   
     */
    protected function siteConfSetSingleItem($configNamespace, $newValue)
    {
        $siteId = Revship::ext("siteRouter")->getSiteId();
        // set site conf
        $result = Revship::conf($siteId)->setSingleItem($configNamespace, $newValue);
    }
    /*
     * get siteId and try to set site config(db) multiple values,
     * @example Call in a controller: $this->siteConfSetItems('site', $valueArrays)   
     */
    protected function siteConfSetItems($configType, $keyValues)
    {
        $siteId = Revship::ext("siteRouter")->getSiteId();
        // set site conf
        $result = Revship::conf($siteId)->setItems($configType, $keyValues);
    }
    /*
    protected function hookAndDisplay($className, $hookFunction, $tpl)
    {
        $this->hook($className, $hookFunction);
        $this->_tpl->display($tpl);
    }
    protected function hook($className, $hookFunction)
    {
        if(method_exists($className, $hookFunction))
        {
            $a = new $className;
            $a->$hookFunction();
        }
    }
    */
}
