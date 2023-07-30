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
