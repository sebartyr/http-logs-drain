<?php

require_once('config.php');

if (!isset($_SERVER['PHP_AUTH_USER']) 
        || !isset($_SERVER['PHP_AUTH_PW']) 
        || $_SERVER['PHP_AUTH_USER'] != USERNAME
        || $_SERVER['PHP_AUTH_PW'] != PASSWORD) 
{

    header('WWW-Authenticate: Basic realm="HTTP-LOG-DRAIN"');
    header('HTTP/1.1 401 Unauthorized');
    echo 'HTTP/1.1 401 Unauthorized';
    exit;
}
