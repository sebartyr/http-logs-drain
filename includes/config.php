<?php
Class Config
{
    public static $config = array(
        // Http auth configuration
        'username' => "sebartyr",
        'password' => "azerty",

        // Storage mode (sql, csv, log)
        'mode' => 'log',

        // Directory path for log & CSV mode
        'dirpath' => "test",
        
        // Database parameters
        'db' => array(
            'mode' => 'mysql', // mysql or pgsql
            'host'=> "localhost",
            'port' => "3306",
            'dbname' => "http-logs-drain",
            'username' => "root",
            'password' => "",
            'table' => "logs"
            )
    );
}

define("USERNAME", "sebartyr");
define("PASSWORD", "azerty");

define("MODE", "log");

define("DIRPATH", "test");

define("DB_MODE", "mysql");
define("DB_HOST", "localhost");
define("DB_PORT", "3306");
define("DB_NAME", "http-logs-drain");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "");
define("DB_TABLE", "logs");
