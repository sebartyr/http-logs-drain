<?php

require_once('../includes/LogsHandler.class.php');
require_once('../includes/Tools.class.php');
require_once('../includes/config.php');
require_once('../includes/login.php');

header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $mode = (isset($_GET['mode']) && !empty($_GET['mode']))?$_GET['mode']:"log";
    $table = (isset($_GET['table']) && Tools::isValidTableName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";
    $time_delta = (isset($_GET['time']) && !empty($_GET['time']))?$_GET['time']:"";
    $limit = (isset($_GET['limit']) && is_numeric($_GET['limit']))?intval($_GET['limit']):((empty($date_before) && empty($date_after) && empty($time_delta))?20:0);
    $reverse = (isset($_GET['reverse']))?true:false;

    $lc = new LogsHandler($table, $date_before, $date_after, $time_delta, $mode);

    $res = $lc->stream($limit, $reverse);

    if(!empty($res)) 
    {
        $message = 'Logs have been streamed';
        syslog(LOG_INFO, $message);
        echo $res;
    }
    else
    {
        $message = 'An error occured';
        syslog(LOG_ERR, $message);
        echo '{"status": "'.$message.'"}';
    }
}
else
{
    $message = 'An error occured : not a GET method';
    syslog(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}