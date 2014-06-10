<?php
class Revship_System
{
    /**
     * Method to execute a command in the terminal
     * Uses :
     *  exec
     *  system
     *  passthru
     *  shell_exec
     */
    public function exec($command)
    {
        //exec
        if(function_exists('exec'))
        {
            exec($command , $output , $return_var);
            $output = implode("\n" , $output);
        }
        //system
        else if(function_exists('system'))
        {
            ob_start();
            system($command , $return_var);
            $output = ob_get_contents();
            ob_end_clean();
        }

        //passthru
        else if(function_exists('passthru'))
        {
            ob_start();
            passthru($command , $return_var);
            $output = ob_get_contents();
            ob_end_clean();
        }
        //shell_exec
        else if(function_exists('shell_exec'))
        {
            $output = shell_exec($command) ;
        }
        else
        {
            return null;
            $output = 'Command execution not possible on this system';
            $return_var = 1;
        }
        return $output;
    }
}