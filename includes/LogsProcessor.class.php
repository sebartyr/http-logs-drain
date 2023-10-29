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
    private string $prefix;
    private string $dirpath;
    private string $filename;

    public function __construct(string $mode, string $raw_logs = "")
    {
        $this->raw_logs = $raw_logs;
        $this->mode = $mode;

        $this->prefix = "";
        $this->dirpath = "";
        $this->filename = "";

        if(!empty($raw_logs)) $this->logs = new Logs($this->raw_logs);
    }

    public function setLogs(array $logs) : void
    {
        $this->logs = new Logs();
        $this->logs->setLogs($logs);
    }

    public function getFullFilename() : string
    {
        return $this->prefix.$this->filename;
    }

    public function getDirpath() : string
    {
        return $this->dirpath;
    }

    public function write($dirpath = DIRPATH, $prefix = "", $filename = "") : bool
    {
        if($this->logs != NULL && $this->logs->isValidated())
        {    
            if($this->mode != 'sql')
            {
                $this->prefix = (isset($_GET['prefix']) && Tools::isValidFilename($_GET['prefix']))?$_GET['prefix'].'-':$prefix;
                $this->dirpath = (isset($_GET['dirpath']) && Tools::isValidDirpath($_GET['dirpath']))?$_GET['dirpath']:$dirpath;
                
                $filename = (isset($_GET['filename']) && Tools::isValidFilename($_GET['filename']))?$_GET['filename']:$filename;
                $this->filename = (!empty($filename))?$filename:'logs-'.date("Y-m-d");

                if(!empty($this->dirpath))
                {
                    if(!is_dir($this->dirpath))
                    {
                        if(!mkdir($this->dirpath, recursive:true)) return false;
                    }
                }
                else
                {
                    $this->dirpath = ".";
                }

                switch($this->mode)
                {
                case "log":
                    $this->filename .= '.log';
                    return $this->writeLogFile();
                    break;
                case "csv":
                    $this->filename .= '.csv';
                    return $this->writeCSVFile();
                    break;
                case "json":
                    $this->filename .= '.json';
                    return $this->writeJSONFile();
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
    
    private function writeCSVFile() : bool
    {
        $filepath = $this->dirpath.'/'.$this->getFullFilename();
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

    private function writeLogFile() : bool
    {
        $filepath = $this->dirpath.'/'.$this->getFullFilename();
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

    private function writeJSONFile() : bool
    {
        $filepath = $this->dirpath.'/'.$this->getFullFilename();

        $lock = new Lock(filepath: $filepath);
        if($lock->lock())
        {
            if(file_exists($filepath))
            {
                $content = json_decode(file_get_contents($filepath));
                $this->logs->setLogs(array_merge($content, $this->logs->getLogs()));
            }

            $f = fopen($filepath, "w");
            
            if(fwrite($f, json_encode($this->logs->getLogs())))
            {
                return $lock->unlock() && fclose($f);
            }
        }

        syslog(LOG_ERR, "Error: writeJSONFile");
        return false;
    }

    private function writeSQL() : bool
    {
        require('db_connect.php');

        $logs = $this->logs->getLogs();
        $table = (isset($_GET['table']) && Tools::isValidTableName($_GET['table']))?$_GET['table']:DB_TABLE;

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
                if(!($req->execute(array("id" => uniqid().dechex(random_int(0,4095)), "date" => $l['date'], 'instanceid' => $l['instanceid'], "logsinfo" => $l['logsinfo'])) && $req->closeCursor())) 
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