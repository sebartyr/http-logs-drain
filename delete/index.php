<?php
require_once(__DIR__.'/../includes/class/LogsHandler.class.php');
require_once(__DIR__.'/../includes/utils/Tools.class.php');
require_once(__DIR__.'/../includes/config/config.php');
require_once(__DIR__.'/../includes/utils/login.php');
require_once(__DIR__.'/../includes/utils/Logging.class.php');

use HttpLogsDrain\Utils\Logging;
use HttpLogsDrain\Utils\Tools;
use HttpLogsDrain\LogsHandler;

header("Content-Type: application/json");

Logging::setFormat(LOG_FORMAT);

if($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
    $table = (isset($_GET['table']) && Tools::isValidTableName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";
    $time_delta = (isset($_GET['time']) && !empty($_GET['time']))?$_GET['time']:"";

    try
    {
        $le = new LogsHandler($table, $date_before, $date_after, $time_delta);

        $prefix = '[mode="sql", table="'.$table.'"] ';

        if($le->erase())
        {
            $message = 'Logs have been deleted';
            Logging::log(LOG_INFO, $prefix.$message);
            echo '{"status": "'.$message.'", "number of deleted rows": "'.$le->getNbHandledRows().'"}';
        }
        else
        {
            $message = 'An error occured: no logs have been deleted (path="'.$_SERVER['REQUEST_URI'].'")';
            Logging::log(LOG_ERR, $prefix.$message);
            echo '{"status": "'.$message.'"}';
        }
    }
    catch(Exception $e)
    {
        Logging::log(LOG_ERR, $e->getMessage());
    }
}
else
{
    $message = 'An error occured : not a DELETE method';
    Logging::log(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}