<?php
require_once('includes/LogsProcessor.class.php');
require_once('includes/config.php');

if (isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW']))
{
    if($_SERVER['PHP_AUTH_USER'] == Config::$config['username'] && $_SERVER['PHP_AUTH_PW'] == Config::$config['password']) {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $post_content = file_get_contents('php://input');
            if(!empty($post_content))
            {
                $lp = new LogsProcessor($post_content, Config::$config['mode']);
                if($lp->write())
                {
                    $message = 'Logs have been saved';
                    syslog(LOG_INFO, $message);
                    echo $message;
                }
                else
                {
                    $message = 'An error occured';
                    syslog(LOG_ERR, $message);
                    echo $message;
                }
            }
            else
            {
                $message = 'An error occured : post content is empty';
                syslog(LOG_ERR, $message);
                echo $message;
            }
        }
        exit;
    }
}

header('WWW-Authenticate: Basic realm="HTTP-LOG-DRAIN"');
header('HTTP/1.0 401 Unauthorized');
echo 'HTTP/1.0 401 Unauthorized';