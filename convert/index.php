<?php

require_once(__DIR__.'/../includes/class/LogsHandler.class.php');
require_once(__DIR__.'/../includes/class/Tools.class.php');
require_once(__DIR__.'/../includes/config/config.php');
require_once(__DIR__.'/../includes/utils/login.php');
require_once(__DIR__.'/../includes/utils/Logging.class.php');

header("Content-Type: application/json");

Logging::setFormat(LOG_FORMAT);

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $mode = (isset($_GET['mode']) && !empty($_GET['mode']))?$_GET['mode']:"log";
    $table = (isset($_GET['table']) && Tools::isValidTableName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";
    $time_delta = (isset($_GET['time']) && !empty($_GET['time']))?$_GET['time']:"";

    $lc = new LogsHandler($table, $date_before, $date_after, $time_delta, $mode);

    $res = $lc->convert();

    if(!empty($res)) 
    {
        $message = '[mode="sql", table="'.$table.'"] Logs have been converted';
        Logging::log(LOG_INFO, $message);
        echo '{"status": "'.$message.'", "link": "'.$res.'"}';
    }
    else
    {
        $message = '[mode="sql", table="'.$table.'"] An error occured (path="'.$_SERVER['REQUEST_URI'].'")';
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