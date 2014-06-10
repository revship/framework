<?php 
Revship::getLibClass('revship.cron.base');
class Revship_Cron
{
    protected $tasks;
    protected $executedTasksArray = array();
    protected $skippedTasksArray = array();
    protected $failedTasksArray = array();
    protected $errorMsgArray = array();
    protected $totalStartTime = null;
    public function __construct()
    {
        $this->totalStartTime = time();
        $this->initAllTasksList();
    }
    
    protected function initAllTasksList()
    {
        $list = Revship::lib('file')->getFiles( CRON_PATH, '.class.php');
        foreach ($list as & $item)
        {
            $item = str_replace('.class.php', '', $item);
        }
        $this->tasks = $list;
    }
    protected function runAllTasks()
    {
        foreach ($this->tasks as $task)
        {
            $this->runTask($task);
        }
    }
    protected function runTask($task)
    {
        $taskFileName = CRON_PATH. $task .'.class.php';
        if(file_exists($taskFileName))
        {
            require_once $taskFileName;
            $className = 'Revship_Cron_Task_'.ucfirst($task);
            $taskObj = new $className;
            if( is_subclass_of($taskObj, 'Revship_Cron_Base') )
            {
                try
                {
                    if($taskObj->run())
                    {
                        $this->executedTasksArray[] = $className;//$task;
                        Revship::log(__METHOD__.'-'. $className, 'CRON_OK');
                    }
                    else
                   {
                       $this->skippedTasksArray[] = $className;//$task;
                    }
                }
                catch(Revship_Exception $e)
                {
                    $this->failedTasksArray[] = $className;//$task;
                    $msg = 'Task Failure: '.$taskFileName.'. Msg:'.$e->getMessage();
                    $this->errorMsgArray[] = $msg;
                    Revship::log(__METHOD__.'-'. $msg, 'CRON_ERROR');
                }
            }
            else
            {
                $this->failedTasksArray[] = $className;//$task;
                $msg = $className.' is not a task class.';
                $this->errorMsgArray[] = $msg;
                Revship::log(__METHOD__.'-'.$msg , 'CRON_ERROR');
                //trigger_error($className.' is not a task class.');
            }
        }
        else
        {
            $this->failedTasksArray[] = $className;//$task;
            $msg = $taskFileName.' is not found.';
            $this->errorMsgArray[] = $msg;
            Revship::log(__METHOD__.'-'.$msg , 'CRON_ERROR');
            //trigger_error($taskFileName.' is not found.');
        } 
    }
    public function run($task = null)
    {
        if($task == null)
        {
            return $this->runAllTasks();
        }
        else
        {
            return $this->runTask($task);
        }
    }
    public function __destruct()
    {
        $output = array(
            'Executed Tasks'=>$this->executedTasksArray,
            'Skipped Tasks'=>$this->skippedTasksArray,
            'Failed Tasks'=>$this->failedTasksArray,
            'Message'=>$this->errorMsgArray,
            'Total Time'=>(time()-$this->totalStartTime),
        );
        echo json_encode($output);
    }

}
