<?php
//set_time_limit(60);
class Revship_Csv
{
    /**
     * Output CSV file From a hashed array
     * @param string $filename
     * @param array $dataArray array(
     *                                            array('name'=>'David', 'age'=>25),
     *                                            array('name'=>'Joy', 'age'=>30),
     *                                            )
     */
    public function outputCsvFromHashArray($filename = 'revship.csv', array $dataArray)
    {
        if(empty($dataArray))
        {
            return false;
        }
        $tableHead = array_keys($dataArray[0]);
        $this->outputCsv ($filename, $dataArray, $tableHead );
    } 
    /**
     * Output CSV file
     * @param string $filename
     * @param array $dataArray array(
     *                                            array('David', 25),
     *                                            array('Joy', 30),
     *                                            )
     * @param array $tableHead array('Name', 'Age');
     */
    public function outputCsv ($filename = 'revship.csv', array $dataArray, array $tableHead = array() )
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename={$filename}");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        //Output table head
        if(count($tableHead))
        {
            foreach($tableHead as & $col)
            {
                $this->specialCharEncode($col);
            }
            echo implode(',', $tableHead) . "\n";
        }
        //Output data
        foreach ($dataArray as & $line) 
        {
            //Process if it has comma, or other special chars
            foreach($line as & $col)
            {
                $this->specialCharEncode($col);
            }
            echo implode(',', $line) . "\n";
        }
    }
    /**
     * Add quote if special char appeared
     */
    protected function specialCharEncode(& $string)
    {
        $needQuotes = false;
        if(strstr($string, ',') || strstr($string, '"') || strstr($string, "\n") || strstr($string, "\r") || strstr($string, "") )
        {
            $needQuotes = true;
        }
        if(strstr($string, '"'))
        {
            $string = str_replace('"', '""', $string);
        }
        if($needQuotes)
        {
            $string = '"' . $string . '"';
        }
        return $string;
    }
    protected function specialCharDecode($string)
    {
        if( substr($string,0,1) =='"' && substr($string,-1,1) == '"' )
        {
            $string = substr($string,1);
            $string = substr($string,0,-1);
        }
        $string = str_replace('""', '"', $string);
        return $string;
    }
    /**
     * Convert CSV to Array
     * 
     * @param string $csvString    name,age\nDavid,25\nJoy,30
     * @return array            array(
     *                                            array('name'=>'David', 'age'=>25),
     *                                            array('name'=>'Joy', 'age'=>30),
     *                                            )
     */
    public function convertCsvToHashArray($csvString)
    {
        /*
        $targetArray = explode("\n", $csvString);
        $keysString = array_shift($targetArray);
        $keysColArray = explode(",", $string);
        */
    }
    
    
}

