<?php

use Monolog\Logger;




/**
 * Heritage de la configuration
 ****************************************************/
require __DIR__ . '/prod.php';

$app['environnement'] = 'DEV';
/****************************************************/




/**
 * Monologue
 ****************************************************/
$app['monolog.level'] = Logger::DEBUG;
/****************************************************/




/**
 * Doctrine
 ****************************************************/
$app['dbs.options'] = array(
    'Stage_db' => array(
        'driverClass' => 'Drivers\PDODblib\Driver',
        'port'      => '1433',
        'host'      => 'WEBDEV-DTC-DC',
        'dbname'    => 'DEV_FU',
        'user'      => 'intra1',
        'password'  => 'intra1',
    ),
);
/****************************************************/




/**
 * Configuration spécifique de l'application
 ****************************************************/
// Activation du mode débogage
$app['debug'] = true;

// Affichage des erreurs
error_reporting(E_ALL | E_STRICT); 
ini_set('display_errors', 1);
ini_set('log_errors', 1);
/****************************************************/