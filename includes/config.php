<?php
Class Config
{
    public static $config = array(
        //http auth
        'username' => "sebartyr",
        'password' => "azerty",

        //writing mode (sql, csv, text)
        'mode' => 'csv',

        //dir path for text & CSV mode
        'dirpath' => "test",
        
        //db parameters
        'db' => array(
            'mode' => 'mysql',
            'host'=> "localhost",
            'port' => "3306",
            'dbname' => "http-logs-drain",
            'username' => "root",
            'password' => "",
            'table' => "logs"
            )
    );
}
