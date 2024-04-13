<?php

require_once('../includes/LogsHandler.class.php');
require_once('../includes/Tools.class.php');
require_once('../includes/config.php');
require_once('../includes/login.php');
require_once('../includes/utils/Logging.class.php');

header("Content-Type: application/json");

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

    if(!empty($res)) 
    {

        $message = '[mode="sql", table="'.$table.'"] Logs have been streamed';
        Logging::log(LOG_INFO, $message);
        echo $res;
    }
    else
    {
        $message = '[mode="sql", table="'.$table.'"] An error occured (path="'.$_SERVER['REQUEST_URI'].')';
        Logging::log(LOG_ERR, $message);
        echo '{"status": "'.$message.'"}';
    }
}
else
{
    $message = 'An error occured : not a GET method';
    Logging::log(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}