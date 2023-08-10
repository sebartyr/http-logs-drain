<?php
require_once('LogsProcessor.class.php');
require_once('Tools.class.php');
require_once('config.php');

class LogsConverter
{

    private LogsProcessor $lp;
    private string $mode;
    private string $table;
    private string $date_before;
    private string $date_after;

    public function __construct(string $mode = 'log', $table = DB_TABLE, $date_before = "", $date_after = "")
    {
        $this->mode = $mode;
        $this->table = $table;
        $this->date_before = (Tools::isValidDate($date_before))?$date_before:"";
        $this->date_after = (Tools::isValidDate($date_after))?$date_after:"";;
    }

    public function convert() : string
    {
        require('db_connect.php');

        $date_after = (!empty($this->date_after))?$this->date_after:'1900-01-01T00:00:00.000Z';
        $date_before = (!empty($this->date_before))?$this->date_before:'9999-12-31T23:59:59.999Z';


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
            $req->execute(array("date_after" => $date_after, "date_before" => $date_before));
            if($data = $req->fetchAll(PDO::FETCH_ASSOC))
            {
                $this->lp = new LogsProcessor($this->mode);
                $this->lp->setLogs($data);
                
                $dirpath = "../converted-logs";
                $filename = 'converted-logs-'.date("Y-m-d_H-i-s");

                if($this->lp->write($dirpath, "", $filename))
                {
                    $proto = (!empty($_SERVER['https']))?"https":"http";
                    $port = ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443)?':'.$_SERVER['SERVER_PORT']:"";
                    return $proto.'://'.$_SERVER['SERVER_NAME'].$port.'/'.basename($dirpath).'/'.$this->lp->getFilename();
                }

            }
        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }

        syslog(LOG_ERR, "Error: converting logs");
        return "";
    }
}