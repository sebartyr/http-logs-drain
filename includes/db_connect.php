<?php
require_once('config.php');

// Connexion à la base de données
try
{
    $bdd = new PDO(Config::$config['db']['mode'].':host='.Config::$config['db']['host'].';port='.Config::$config['db']['port'].';dbname='.Config::$config['db']['dbname'], 
	Config::$config['db']['username'], 
	Config::$config['db']['password'],
	array(PDO::ATTR_PERSISTENT => true));

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// $bdd->query("SET CHARACTER SET 'latin1'");
	$bdd->query("SET NAMES 'utf8'");
}
catch(Exception $e)
{
	die('Erreur : '.$e->getMessage());
} 
?>