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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $table = (isset($_GET['table']) && Tools::isValidTableName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";
    $time_delta = (isset($_GET['time']) && !empty($_GET['time']))?$_GET['time']:"";
    $limit = (isset($_GET['limit']) && is_numeric($_GET['limit']))?intval($_GET['limit']):((empty($date_before) && empty($date_after) && empty($time_delta))?20:0);
    $reverse = (isset($_GET['reverse']))?true:false;

    $lc = new LogsHandler($table, $date_before, $date_after, $time_delta);

    $res = $lc->stream($limit, $reverse);

    $prefix = '[mode="sql", table="'.$table.'"] ';

    if(!empty($res)) 
    {

        $message = 'Logs have been streamed';
        Logging::log(LOG_INFO, $prefix.$message);
        echo $res;
    }
    else
    {
        $message = 'An error occured (path="'.$_SERVER['REQUEST_URI'].')';
        Logging::log(LOG_ERR, $prefix.$message);
        echo '{"status": "'.$message.'"}';
    }
}
else
{
    $message = 'An error occured : not a GET method';
    Logging::log(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}