<?php
/** 
 * @author SLJ
 * 
 * 
 */
class Revship_Subdomain
{
	/*
	 * Bool type, indicates if it is now visiting a subdomain
	 */
    protected $hasSubdomain = false;
    /*
     * The subdomain which is now visiting
     */
    protected $subdomain = null;
    /*
     * Extra condition field for db query from subdomain
     */
    protected $condField = 'city_id'; 
    /*
     * Extra condition value for db query from subdomain
     */
    protected $condValue = null;

    /**
     * Initialize 
     */
    public function __construct()
    {
        /*
        if(Revship::lib('uri')->getSubdomain())
        {
            $this->subdomain = Revship::lib('uri')->getSubdomain();
            $this->hasSubdomain = true;
        }
        //Subdomain condition field ('city_id' is default)
        if(Revship::lib('config')->getItem('site.subdomain.condField'))
        {
        	// Get subdomain condition field
        	$this->condField = Revship::lib('config')->getItem('site.subdomain.condField');
        	// Get subdomain condition value
        	$condValueArray = Revship::lib('config')->getItem('site.subdomain.condValue');
        	if( array_key_exists($this->subdomain, $condValueArray) )
        	{
        		$this->condValue = $condValueArray[$this->subdomain];
        	}
        	else
        	{
        		// Not found the condition value, then clear condField to prevent error
        		$this->condField = null;
        	}
        	
        		
        }
        */
    }
    /**
     * Merge subdomain condition array to an existing condition array
     * 
     * @param array $condArray
     */
    public function mergeSubdomainCondition( &$condArray)
    {
        /*
	    	if( ! $this->hasSubdomain || ! $this->condValue || ! $this->condField ) 
	    	{
	    		return false;
	    	}
	    		
	    	if( ! array_search($this->condField, $condArray))
	    	{
	    		$extraCond = array(
	    							$this->condField .'='. $this->condValue
	    							);
	        	$condArray = array_merge($condArray, $extraCond);
	    	}
	    	*/
    }
}
?>