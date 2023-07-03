<?php
require_once('Logs.class.php');
require_once('Lock.class.php');
require_once('config.php');

class LogsProcessor
{

    private string $raw_logs;
    private Logs $logs;
    private string $mode;

    public function __construct(string $raw_logs, string $mode)
    {
        $this->raw_logs = $raw_logs;
        $this->mode = $mode;
        $this->logs = $this->processRawLog();
    }

    private function processRawLog() : Logs
    {
        $logs = new Logs($this->raw_logs);

        return $logs;
    }

    public function write() : bool
    {
        if($this->logs != NULL && $this->logs->isValidated())
        {
            $prefix = (isset($_GET['prefix']) && !empty($_GET['prefix']))?$_GET['prefix'].'-':"";

            $dirpath = Config::$config['dirpath'];

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
    
    public function writeCSVFile(string $dirpath, string $prefix) : bool
    {
        $f = fopen($dirpath.'/'.$prefix.'logs-'.date("Y-m-d").'.csv', "a+");
        $lock = new Lock($f);

        if($lock->lock())
        {
            if(fputcsv($f, $this->logs->getLogs(), ';'))
            {
                return $lock->unlock() && fclose($f);
            }
        }

        syslog(LOG_ERR, "Error: writeCSVFile");
        return false;
    }

    public function writeLogFile(string $dirpath, string $prefix) : bool
    {
        $f = fopen($dirpath.'/'.$prefix.'logs-'.date("Y-m-d").'.log', "a+");
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

    public function writeSQL() : bool
    {
        require_once('db_connect.php');

        $l = $this->logs->getLogs();
        $table = (isset($_GET['table']) && !empty($_GET['table']))?$_GET['table']:Config::$config['db']['table'];

        $req = $bdd->prepare('INSERT INTO '.$table.'(id, date, instanceId, logsInfo) VALUES(:id, :date, :instanceId, :logsInfo)');
        if($req->execute(array("id" => uniqid(), "date" => $l['date'], 'instanceId' => $l['instanceId'], "logsInfo" => $l['logsInfo'])) && $req->closeCursor()) 
        {
            return true;
        }
        
        syslog(LOG_ERR, "Error: writeSQL");
        return false;
    }
}