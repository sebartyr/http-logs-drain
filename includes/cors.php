<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') 
{
    exit();
}