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

    public function __construct($table = DB_TABLE, $date_before = "", $date_after = "", string $mode = 'log')
    {

        $this->nb_handled_rows = 0;

        $this->mode = $mode;
        $this->table = $table;

        $this->date_after = (!empty($date_after))?$date_after:'1900-01-01T00:00:00.000Z';
        $this->date_before = (!empty($date_before))?$date_before:'9999-12-31T23:59:59.999Z';
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
                    return $proto.'://'.$_SERVER['SERVER_NAME'].$port.'/convertlogs/'.$lp->getDirpath().'/'.$lp->getFullFilename();
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
}