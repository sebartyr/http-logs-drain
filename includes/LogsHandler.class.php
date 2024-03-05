<?php
require_once('LogsProcessor.class.php');
require_once('config.php');

class LogsHandler
{
    private string $mode;
    private string $table;
    private string $date_before;
    private string $date_after;
    private int $nb_handled_rows;

    public function __construct($table = DB_TABLE, $date_before = "", $date_after = "", string $time_delta = "", string $mode = 'log')
    {

        $this->nb_handled_rows = 0;

        $this->mode = $mode;
        $this->table = $table;

        $this->date_after = $date_after;
        $this->date_before = $date_before;

        $this->convertTimeDelta($time_delta);

        $this->date_after = (!empty($this->date_after))?$this->date_after:'1900-01-01T00:00:00.000Z';
        $this->date_before = (!empty($this->date_before))?$this->date_before:'9999-12-31T23:59:59.999Z';     
    }

    public function convert() : string
    {
        require('db_connect.php');

        try
        {
            switch(DB_MODE)
            {
                case "pgsql":
                    $req_string = 'SELECT "date", "instanceid", "logsinfo" FROM "'.$this->table.'" WHERE "date" > :date_after AND "date" < :date_before ORDER BY "date" ASC';
                    break;
                default:
                    $req_string = 'SELECT `date`, `instanceid`, `logsinfo` FROM `'.$this->table.'` WHERE `date` > :date_after AND `date` < :date_before ORDER BY `date` ASC';
            }

            $req = $bdd->prepare($req_string);
            $req->execute(array("date_after" => $this->date_after, "date_before" => $this->date_before));
            if($data = $req->fetchAll(PDO::FETCH_ASSOC))
            {
                $lp = new LogsProcessor($this->mode);
                $lp->setLogs($data);

                $dirpath = "converted-logs";
                $filename = 'converted-logs-'.date("Y-m-d_H-i-s");

                if($lp->write($dirpath, "", $filename))
                {
                    $proto = (!empty($_SERVER['https']))?"https":"http";
                    $port = ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443)?':'.$_SERVER['SERVER_PORT']:"";
                    return $proto.'://'.$_SERVER['SERVER_NAME'].$port.'/convert/'.$lp->getDirpath().'/'.$lp->getFullFilename();
                }

            }
        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }

        syslog(LOG_ERR, "Error: cannot convert logs");
        return "";
    }

    public function stream(int $limit = 20, bool $reverse = false) : string
    {
        require('db_connect.php');

        try
        {
            switch(DB_MODE)
            {
                case "pgsql":
                    $req_string = 'SELECT "date", "instanceid", "logsinfo" FROM "'.$this->table.'" WHERE "date" > :date_after AND "date" < :date_before ORDER BY "date" DESC'.(($limit)?" LIMIT :limit":"");
                    if(!$reverse) $req_string = 'SELECT * FROM ('.$req_string.') AS sub ORDER BY "date" ASC';
                    break;
                default:
                    $req_string = 'SELECT `date`, `instanceid`, `logsinfo` FROM `'.$this->table.'` WHERE `date` > :date_after AND `date` < :date_before ORDER BY `date` DESC'.(($limit)?" LIMIT :limit":"");
                    if(!$reverse) $req_string = 'SELECT * FROM ('.$req_string.') AS sub ORDER BY `date` ASC';
            } 

            $req = $bdd->prepare($req_string);
            $req->bindParam(":date_after", $this->date_after, PDO::PARAM_STR);
            $req->bindParam(":date_before", $this->date_before, PDO::PARAM_STR);
            if($limit) $req->bindParam(":limit", $limit, PDO::PARAM_INT);
            $req->execute();         

            if($data = $req->fetchAll(PDO::FETCH_ASSOC))
            { 
                return json_encode($data);
            }

        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }
        
        syslog(LOG_ERR, "Error: cannot stream logs");
        return "";
    }

    public function erase() : bool
    {
        require('db_connect.php');      

        try
        {
            switch(DB_MODE)
            {
                case "pgsql":
                    $req_string = 'DELETE FROM "'.$this->table.'" WHERE "date" > :date_after AND "date" < :date_before';
                    break;
                default:
                    $req_string = 'DELETE FROM `'.$this->table.'` WHERE `date` > :date_after AND `date` < :date_before';
            }

            $req = $bdd->prepare($req_string);
            $req->execute(array("date_after" => $this->date_after, "date_before" => $this->date_before));

            $this->nb_handled_rows = $req->rowCount();

            if($this->nb_handled_rows > 0) return true;
            
        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }

        syslog(LOG_ERR, "Error: cannot delete logs");
        return false;
    }

    public function getNbHandledRows() : int
    {
        return $this->nb_handled_rows;
    }

    private function convertTimeDelta(string $time_delta) : void
    {
        $m = [];
        if(preg_match("/^([-]?)([0-9]+)([dhm])$/", $time_delta, $m))
        {
            $nb = (int) $m[2];
            $factor = 0;

            switch($m[3])
            {
                case 'm':
                    $factor = 60;
                    break;
                
                case 'h':
                    $factor = 3600;
                    break;

                case 'd':
                    $factor = 86400;
                    break;
            }

            $delta_timestamp = $nb*$factor;

            date_default_timezone_set('UTC');
            $timestamp = time() - $delta_timestamp;
            $date = date('Y-m-d\TH:i:s.000\Z', $timestamp);

            if($m[1] == '-')
            {
                $this->date_after = $date;
                $this->date_before = "";
            }
            else
            {
                $this->date_after = "";
                $this->date_before = $date;
            }
        }
    }
}