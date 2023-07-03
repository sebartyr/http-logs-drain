<?php

require_once('config.php');

if (!isset($_SERVER['PHP_AUTH_USER']) 
        || !isset($_SERVER['PHP_AUTH_PW']) 
        || $_SERVER['PHP_AUTH_USER'] != Config::$config['username'] 
        || $_SERVER['PHP_AUTH_PW'] != Config::$config['password']) 
{

    header('WWW-Authenticate: Basic realm="HTTP-LOG-DRAIN"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'HTTP/1.0 401 Unauthorized';
    exit;
}
