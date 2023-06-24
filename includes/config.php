<?php
Class Config
{
    public static $config = array(
        'username' => "sebartyr",
        'password' => "azerty",
        'mode' => 'sql',
        'db' => array(
            'mode' => 'mysql',
            'host'=> "localhost",
            'dbname' => "http-logs-drain",
            'username' => "root",
            'password' => "",
            'table' => "logs"
            )
    );
}
