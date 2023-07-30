<?php
Class Config
{
    public static $config = array(
        // Http auth configuration
        'username' => "sebartyr",
        'password' => "azerty",

        // Storage mode (sql, csv, log)
        'mode' => 'sql',

        // Directory path for log & CSV mode
        'dirpath' => "test",
        
        // Database parameters
        'db' => array(
            'mode' => 'mysql', // mysql or pgsql
            'host'=> getenv("MYSQL_ADDON_HOST"),
            'port' => getenv("MYSQL_ADDON_PORT"),
            'dbname' => getenv("MYSQL_ADDON_DB"),
            'username' => getenv("MYSQL_ADDON_USER"),
            'password' => getenv("MYSQL_ADDON_PASSWORD"),
            'table' => "logs"
            )
    );
}
