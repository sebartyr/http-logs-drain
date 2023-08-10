<?php
require_once('Logs.class.php');
require_once('Lock.class.php');
require_once('Tools.class.php');
require_once('config.php');

class LogsProcessor
{

    private string $raw_logs;
    private Logs $logs;
    private string $mode;
    private string $filename;

    public function __construct(string $mode, string $raw_logs = "")
    {
        $this->raw_logs = $raw_logs;
        $this->mode = $mode;

        if(!empty($raw_logs)) $this->logs = new Logs($this->raw_logs);
    }

    public function setLogs(array $logs) : void
    {
        $this->logs = new Logs();
        $this->logs->setLogs($logs);
    }

    public function getFilename() : string
    {
        return $this->filename;
    }

    public function write($dirpath = DIRPATH, $prefix = "", $filename = "") : bool
    {
        if($this->logs != NULL && $this->logs->isValidated())
        {    
            echo 'OK';
            if($this->mode != 'sql')
            {
                $prefix = (isset($_GET['prefix']) && Tools::isValidName($_GET['prefix']))?$_GET['prefix'].'-':$prefix;
                $dirpath = (isset($_GET['dirpath']) && Tools::isValidName($_GET['dirpath']))?$_GET['dirpath']:$dirpath;
                $filename = (isset($_GET['filename']) && Tools::isValidName($_GET['filename']))?$_GET['filename']:$filename;

                if(!empty($dirpath))
                {
                    if(!is_dir($dirpath))
                    {
                        if(!mkdir($dirpath, recursive:true)) return false;
                    }
                }
                else
                {
                    $dirpath = ".";
                }

                switch($this->mode)
                {
                case "log":
                    return $this->writeLogFile($dirpath, $prefix, (!empty($filename))?$filename.'.log':'logs-'.date("Y-m-d").'.log');
                    break;
                case "csv":
                    return $this->writeCSVFile($dirpath, $prefix, (!empty($filename))?$filename.'.csv':'logs-'.date("Y-m-d").'.csv');
                    break;
                }
            }
            else
            {
                return $this->writeSQL();
            }
        }

        syslog(LOG_ERR, "Error: invalid log format");
        return false;
    }
    
    private function writeCSVFile(string $dirpath, string $prefix, string $filename) : bool
    {
        $this->filename = $filename;
        $filepath = $dirpath.'/'.$prefix.$filename;
        $f = fopen($filepath, "a+");
        $lock = new Lock($f);

        $no_error = true;

        if($lock->lock())
        {
            foreach($this->logs->getLogs() as $logs)
            {
                if(!fputcsv($f, $logs, ';'))
                {
                    syslog(LOG_ERR, "Error: writeCSVFile");
                    $no_error = false;
                }
            }

            return $lock->unlock() && fclose($f) && $no_error;
        }

        syslog(LOG_ERR, "Error: writeCSVFile");
        return false;
    }

    private function writeLogFile(string $dirpath, string $prefix, string $filename) : bool
    {
        $this->filename = $filename;
        $filepath = $dirpath.'/'.$prefix.$filename;
        $f = fopen($filepath, "a+");
        $lock = new Lock($f);

        if($lock->lock())
        {
            if(fwrite($f, $this->logs->toString()))
            {
                return $lock->unlock() && fclose($f);
            }
        }

        syslog(LOG_ERR, "Error: writeLogFile");
        return false;
    }

    private function writeSQL() : bool
    {
        require('db_connect.php');

        $logs = $this->logs->getLogs();
        $table = (isset($_GET['table']) && Tools::isValidName($_GET['table']))?$_GET['table']:DB_TABLE;

        $no_error = true;

        try
        {
            switch(DB_MODE)
            {
                case "pgsql":
                    $req_string = 'INSERT INTO "'.$table.'"("id", "date", "instanceid", "logsinfo") VALUES(:id, :date, :instanceid, :logsinfo)';
                    break;
                default:
                    $req_string = 'INSERT INTO `'.$table.'`(`id`, `date`, `instanceid`, `logsinfo`) VALUES(:id, :date, :instanceid, :logsinfo)';
            }
            
            $req = $bdd->prepare($req_string);

            foreach($logs as $l)
            {
                if(!($req->execute(array("id" => uniqid(), "date" => $l['date'], 'instanceid' => $l['instanceid'], "logsinfo" => $l['logsinfo'])) && $req->closeCursor())) 
                {
                    syslog(LOG_ERR, "Error: writeSQL");
                    $no_error = false;
                }
            }
        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }
        
        return $no_error;
    }
}