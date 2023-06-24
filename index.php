<?php
require_once('includes/LogsProcessor.class.php');
require_once('includes/config.php');

if (isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW']))
{
    if($_SERVER['PHP_AUTH_USER'] == Config::$config['username'] && $_SERVER['PHP_AUTH_PW'] == Config::$config['password']) {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $lp = new LogsProcessor(file_get_contents('php://input'), Config::$config['mode']);
            if($lp->write())
                echo 'done';
        }
        exit;
    }
}

header('WWW-Authenticate: Basic realm="HTTP-LOG-DRAIN"');
header('HTTP/1.0 401 Unauthorized');
echo 'HTTP/1.0 401 Unauthorized';