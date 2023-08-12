<?php
require_once('../includes/LogsEraser.class.php');
require_once('../includes/Tools.class.php');
require_once('../includes/config.php');
require_once('../includes/login.php');

header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
    $table = (isset($_GET['table']) && Tools::isValidName($_GET['table']))?$_GET['table']:DB_TABLE;
    $date_before = (isset($_GET['before']) && Tools::isValidDate($_GET['before']))?$_GET['before']:"";
    $date_after = (isset($_GET['after']) && Tools::isValidDate($_GET['after']))?$_GET['after']:"";

    $le = new LogsEraser($table, $date_before, $date_after);

    if($le->erase())
    {
        $message = 'Logs have been deleted';
        syslog(LOG_INFO, $message);
        echo '{"state": "'.$message.'", "number of deleted rows": "'.$le->getNbDeletedRow().'"}';
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
    $message = 'An error occured : not a DELETE method';
    syslog(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}