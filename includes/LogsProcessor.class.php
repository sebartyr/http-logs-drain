<?php
require_once('Logs.class.php');
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

    public function write() : bool
    {
        if($this->logs != NULL && $this->logs->isValidated())
        {
            $prefix = (isset($_GET['prefix']) && !empty($_GET['prefix']))?$_GET['prefix'].'-':"";

            if($this->mode == "text")
            {
                return $this->writeTextFile($prefix);
            }
            else if($this->mode == "csv")
            {
                return $this->writeCSVFile($prefix);
            }
            else if($this->mode == "sql")
            {
                return $this->writeSQL();
            }
        }

        return false;
    }
    
    public function writeCSVFile(string $prefix) : bool
    {
        $f = fopen($prefix.'logs-'.date("Y-m-d").'.csv', "a+");
        if(flock($f, LOCK_EX))
        {
            if(fwrite($f, $this->logs->toCSVFormat()))
            {
                return fclose($f);
            }
        }

        return false;
    }

    public function writeTextFile(string $prefix) : bool
    {
        $f = fopen($prefix.'logs-'.date("Y-m-d").'.log', "a+");
        if(flock($f, LOCK_EX))
        {
            if(fwrite($f, $this->logs->toString()))
            {
                return fclose($f);
            }
        }

        return false;
    }

    public function writeSQL() : bool
    {
        require_once('db_connect.php');

        $l = $this->logs->getLogs();
        $table = (isset($_GET['table']) && !empty($_GET['table']))?$_GET['table']:Config::$config['db']['table'];

        $req = $bdd->prepare('INSERT INTO '.$table.'(id, date, instanceId, logsInfo) VALUES(:id, :date, :instanceId, :logsInfo)');
        return ($req->execute(array("id" => uniqid(), "date" => $l['date'], 'instanceId' => $l['instanceId'], "logsInfo" => $l['logsInfo'])) 
                && $req->closeCursor());
    }

    private function processRawLog() : Logs
    {
        $logs = new Logs($this->raw_logs);

        return $logs;
    }
}