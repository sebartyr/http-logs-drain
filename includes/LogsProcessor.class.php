<?php
require_once('Logs.class.php');
require_once('Lock.class.php');
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

    public function write($dirpath = DIRPATH) : bool
    {
        if($this->logs != NULL && $this->logs->isValidated())
        {
            $prefix = (isset($_GET['prefix']) && !empty($_GET['prefix']))?$_GET['prefix'].'-':"";
            $dirpath = (isset($_GET['dirpath']) && !empty($_GET['dirpath']))?$_GET['dirpath']:$dirpath;

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
                    return $this->writeLogFile($dirpath, $prefix);
                    break;
                case "csv":
                    return $this->writeCSVFile($dirpath, $prefix);
                    break;
                case "sql":
                    return $this->writeSQL();
                    break;
            }
        }

        syslog(LOG_ERR, "Error: invalid log format");
        return false;
    }
    
    private function writeCSVFile(string $dirpath, string $prefix) : bool
    {
        $this->filename = $dirpath.'/'.$prefix.'logs-'.date("Y-m-d").'.csv';
        $f = fopen($this->filename, "a+");
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

    private function writeLogFile(string $dirpath, string $prefix) : bool
    {
        $this->filename = $dirpath.'/'.$prefix.'logs-'.date("Y-m-d").'.csv';
        $f = fopen($this->filename, "a+");
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
        require_once('db_connect.php');

        $logs = $this->logs->getLogs();
        $table = (isset($_GET['table']) && !empty($_GET['table']))?$_GET['table']:DB_TABLE;

        $no_error = true;

        $req = $bdd->prepare('INSERT INTO '.$table.'(id, date, instanceId, logsInfo) VALUES(:id, :date, :instanceId, :logsInfo)');

        foreach($logs as $l)
        {
            if(!($req->execute(array("id" => uniqid(), "date" => $l['date'], 'instanceId' => $l['instanceId'], "logsInfo" => $l['logsInfo'])) && $req->closeCursor())) 
            {
                syslog(LOG_ERR, "Error: writeSQL");
                $no_error = false;
            }
        }
        
        return $no_error;
    }
}