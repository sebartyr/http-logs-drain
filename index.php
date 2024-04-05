<?php
require_once('includes/LogsProcessor.class.php');
require_once('includes/config.php');
require_once('includes/login.php');

header("Content-Type: application/json");

function logPrefix(LogsProcessor $lp) : string
{
    switch(MODE)
    {
        case "sql":
            $message = '[mode="sql", table="'.$lp->getTableName().'"] ';
            break;
        default:
            $message = '[mode="'.MODE.'", file="'.$lp->getFullFilename().'"] ';
    }

    return $message;
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $post_content = file_get_contents('php://input');
    if(!empty($post_content))
    {
        $lp = new LogsProcessor(MODE, $post_content);

        if($lp->write())
        {
            $message = logPrefix($lp).'Logs have been saved';
            syslog(LOG_INFO, $message);
            echo '{"status": "'.$message.'"}';
        }
        else
        {
            $message = logPrefix($lp).'An error occured (path="'.$_SERVER['REQUEST_URI'].'")';
            syslog(LOG_ERR, $message);
            echo '{"status": "'.$message.'"}';
        }
    }
    else
    {
        $message = 'An error occured : post content is empty';
        syslog(LOG_ERR, $message);
        echo '{"status": "'.$message.'"}';
    }
}
else
{
    $message = 'An error occured : not a POST method';
    syslog(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}