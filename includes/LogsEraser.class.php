<?php
require_once("../includes/Tools.class.php");
require_once('config.php');

class LogsEraser
{

    private string $table;
    private string $date_before;
    private string $date_after;
    private int $nb_deleted_rows;

    public function __construct($table = DB_TABLE, $date_before = "", $date_after = "")
    {
        $this->nb_deleted_rows = 0;

        $this->table = $table;
        $this->date_before = $date_before;
        $this->date_after = $date_after;    
    }

    public function erase() : bool
    {
        require('db_connect.php');

        $date_after = (!empty($this->date_after))?$this->date_after:'1900-01-01T00:00:00.000Z';
        $date_before = (!empty($this->date_before))?$this->date_before:'9999-12-31T23:59:59.999Z';


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
            $req->execute(array("date_after" => $date_after, "date_before" => $date_before));

            $this->nb_deleted_rows = $req->rowCount();

            if($this->nb_deleted_rows > 0) return true;
            
        }
        catch(Exception $e)
        {
            syslog(LOG_ERR, 'Exception PDO : '.$e->getMessage());
        }

        syslog(LOG_ERR, "Error: cannot delete logs");
        return false;
    }

    public function getNbDeletedRow() : int
    {
        return $this->nb_deleted_rows;
    }
}