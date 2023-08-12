<?php

require_once('../includes/LogsConverter.class.php');
require_once('../includes/Tools.class.php');
require_once('../includes/config.php');
require_once('../includes/login.php');

header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $mode = (isset($_GET['mode']) && !empty($_GET['mode']))?$_GET['mode']:"log";
    $table = (isset($_GET['table']) && Tools::isValidName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";

    $lc = new LogsConverter($mode, $table, $date_before, $date_after);

    $res = $lc->convert();

    if(!empty($res)) 
    {
        $message = 'Logs have been converted';
        syslog(LOG_INFO, $message);
        echo '{"state": "'.$message.'", "link": "'.$res.'"}';
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