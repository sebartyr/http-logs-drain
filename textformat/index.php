<?php

require_once('includes/LogsConverter.class.php');
require_once('../includes/config.php');
require_once('../includes/login.php');

header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $mode = (isset($_GET['mode']) && !empty($_GET['mode']))?$_GET['mode']:"log";
    $table = (isset($_GET['table']) && !empty($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && !empty($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && !empty($_GET['after']))?$_GET['after']:"";

    $lc = new LogsConverter();
}
else
{
    $message = 'An error occured : not a GET method';
    syslog(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}