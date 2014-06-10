<?php

defined('REVSHIP') or exit('Access Denied!');

/**
 * Revship Benchmarking
 * @author Lijun Shen <lijunshen@revship.com>
 * @date Apr 22, 2011
 * @since 1.0.0
 */

class Revship_Benchmarking
{
    //Benchmarking
//define('RS_START_TIME', array_sum(explode(' ', microtime())));
    protected $marks = array();
    public function getTotalTime($keepDecimals=4)
    {
        return number_format(array_sum(explode(' ', microtime())) - RS_START_TIME,$keepDecimals);
    }
    public function mark($event = 'Untitled Mark')
    {
        $time = $this->getTotalTime();
        $this->marks[] = array($event,$time);
        return $time;
    }
    
    /**
     * Time Duration between event1 and event2
     * @param $event1  Start Event
     * @param $event2  End Event
     */
    public function getDurationTime($event1, $event2)
    {
        $mark1 = $this->getMarkTime($event1);
        $mark2 = $this->getMarkTime($event2);
        return doubleval($mark2) - doubleval($mark1);
    }
    public function getMarkTime($event)
    {
        foreach ($this->marks as $mark)
        {
            if($mark[0]==$event)
            {
                return $mark[1];
            }
        } 
        return null;
    }
    public function getMarks()
    {
        return $this->marks;
    }
    public function __destruct()
    {
        if(!empty($this->marks))
        {
            Revship::log(__METHOD__.'-'.var_export($this->marks,true));
        }
    }
}