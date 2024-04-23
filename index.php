<?php
require_once(__DIR__.'/includes/class/LogsProcessor.class.php');
require_once(__DIR__.'/includes/config/config.php');
require_once(__DIR__.'/includes/utils/login.php');
require_once(__DIR__.'/includes/utils/Logging.class.php');

use HttpLogsDrain\Utils\Logging;
use HttpLogsDrain\LogsProcessor;

header("Content-Type: application/json");

Logging::setFormat(LOG_FORMAT);

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
            $message = 'Logs have been saved';
            Logging::log(LOG_INFO, logPrefix($lp).$message);
            echo '{"status": "'.$message.'"}';
        }
        else
        {
            $message = 'An error occured (path="'.$_SERVER['REQUEST_URI'].'")';
            Logging::log(LOG_ERR, logPrefix($lp).$message);
            echo '{"status": "'.$message.'"}';
        }
    }
    else
    {
        $message = 'An error occured : post content is empty';
        Logging::log(LOG_ERR, $message);
        echo '{"status": "'.$message.'"}';
    }
}
else
{
    $message = 'An error occured : not a POST method';
    Logging::log(LOG_ERR, $message);
    echo '{"status": "'.$message.'"}';
}