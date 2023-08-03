<?php
require_once('config.php');

// Connexion à la base de données
try
{
    $bdd = new PDO(DB_MODE.':host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true));

    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// $bdd->query("SET CHARACTER SET 'latin1'");
	$bdd->query("SET NAMES 'utf8'");
}
catch(Exception $e)
{
	die('Erreur : '.$e->getMessage());
} 
?>