<?php 

abstract class Revship_Cron_Base
{
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_YEARLY = 'yearly';
    protected $frequency = null;
    protected $defaultFrequency = null;
    protected $configKeyPrefix = null;
    protected $startTime = null;
    /*
     * Particular day allow execute (1 through 31)
     */
    protected $particularDay = array();
    /*
     * Particular weekday allow execute (1 through 7)
     */
    protected $particularWeekday = array();
    /*
     * Particular hour allow execute (0 through 23)
     */ 
    protected $particularHour = array();
    
    abstract public function execute();
    
    /**
     *  In child class, the only thing is set configKeyPrefix
     */
    public function __construct()
    {
        if(!$this->configKeyPrefix)
        {
            throw new Revship_Exception(500,__CLASS__.'::configKey is not set.');
        }
        $this->startTime = time();
        $this->initFrequency();
        $this->loadParticularConfigs();
    }
    public function run()
    {
        if($this->isNeedRun())
        {
            $this->execute();
            $this->saveResult();
            return true;
        }
        return false;
    }
    /**
     * Load Task Frequency From Config
     * If not set, save a default value
     */
    protected function initFrequency()
    {
        // xxxx_frequency
        $key = 'cron.'.$this->configKeyPrefix.'_Frequency';
        $this->frequency = Revship::lib('config')->getItem($key);
        // config not set
        if( empty($this->frequency) )
        {
            if($this->defaultFrequency)
            {
                $this->frequency = $this->defaultFrequency;
            }
            else
            {
                // Set daily as default
                $this->frequency = self::FREQUENCY_DAILY;
            }
            // Save to config file
            Revship::lib('config')->setSingleItem($key, $this->frequency );
        }
    }
    /**
     * Load config from cron.php 
     * to fill the $particularDay $particularWeekday $particularHour
     * as arrays.
     */
    protected function loadParticularConfigs()
    {
        // xxx_ParticularDay
        $key = 'cron.'.$this->configKeyPrefix.'_Day';
        $pDay = Revship::lib('config')->getItem($key);
        if(!empty($pDay))
        {
            $pDay = explode(',', $pDay);
            $this->particularDay = $pDay;
        }
        // xxx_ParticularWeekday
        $key = 'cron.'.$this->configKeyPrefix.'_ParticularWeekday';
        $pWeekday = Revship::lib('config')->getItem($key);
        if(!empty($pWeekday))
        {
            $pWeekday = explode(',', $pWeekday);
            $this->particularWeekday = $pWeekday;
        }
        // xxx_ParticularHour
        $key = 'cron.'.$this->configKeyPrefix.'_ParticularHour';
        $pHour = Revship::lib('config')->getItem($key);
        if(!empty($pHour))
        {
            $pHour = explode(',', $pHour);
            $this->particularHour = $pHour;
        }
    }
    protected function isNeedRun()
    {
        // Get Last Start Time
        $key = 'cron.'.$this->configKeyPrefix.'_LastStartTime';
        $lastStartTime = Revship::lib('config')->getItem($key);
        // Get Frequency
        $key = 'cron.'.$this->configKeyPrefix.'_Frequency';
        $frequency = $this->frequency = Revship::lib('config')->getItem($key);
        // Frequency Check
        switch ($frequency)
        {
            case self::FREQUENCY_HOURLY:
                if( time() - $lastStartTime < 60*60 ) // If < 1 hour, then skip
                {
                    return false;
                }
                break;
            case self::FREQUENCY_DAILY:
                if( time() - $lastStartTime < 60*60*24 ) // If < 1 day, then skip
                {
                    return false;
                }
                break;
            case self::FREQUENCY_WEEKLY:
                if( time() - $lastStartTime < 60*60*24*7 ) // If < 1 week, then skip
                {
                    return false;
                }
                break;
            case self::FREQUENCY_MONTHLY:
                if( $lastStartTime > strtotime('-1 month') ) // If later(larger) than last month the same day, then skip
                {
                    return false;
                }
                break;
            case self::FREQUENCY_YEARLY:
                if( $lastStartTime > strtotime('-1 year') ) // If later(larger) than last year the same day, then skip
                {
                    return false;
                }
            default:
                break;
        }
        // Particular Hour Check
        if( ! empty($this->particularHour) && ! in_array(date('G'), $this->particularHour) )
        {
            return false;
        }
        // Particular Day Check
        if( ! empty($this->particularDay) && ! in_array(date('j'), $this->particularDay) )
        {
            return false;
        }
        // Particular Weekday Check
        if( ! empty($this->particularWeekday) && ! in_array(date('N'), $this->particularWeekday) )
        {
            return false;
        }
        return true;
    }
    protected function saveResult()
    {
        // Save Task Last Start Time
        $key = 'cron.'.$this->configKeyPrefix.'_LastStartTime';
        Revship::lib('config')->setSingleItem($key, $this->startTime );
        // Save Task Last End Time
        $key = 'cron.'.$this->configKeyPrefix.'_LastEndTime';
        Revship::lib('config')->setSingleItem($key, time() );
        // Save Log
    }
}