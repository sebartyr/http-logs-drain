<?php
require_once('includes/LogsProcessor.class.php');
require_once('includes/config.php');
require_once('includes/login.php');

header("Content-Type: application/json");

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $post_content = file_get_contents('php://input');
    if(!empty($post_content))
    {
        syslog(LOG_ERR, "index");
        $lp = new LogsProcessor($post_content, Config::$config['mode']);
        if($lp->write())
        {
            $message = 'Logs have been saved';
            syslog(LOG_INFO, $message);
            echo '{"status": "'.$message.'"}';
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