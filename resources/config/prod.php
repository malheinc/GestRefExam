<?php

use Monolog\Logger;




/**
 * Heritage de la configuration
 ****************************************************/
$app['environnement'] = 'PROD';
/****************************************************/




/**
 * Monologue
 ****************************************************/
$app['monolog.level'] = Logger::ERROR;
$app['monolog.logfile'] = __DIR__ . '/../../var/app.log';
/****************************************************/




/**
 * Doctrine
 ****************************************************/
$app['dbs.options'] = array(
    'Stage_db' => array(
        'driverClass' => 'Drivers\PDODblib\Driver',
        'port'      => '1433',
        'host'      => 'WEBDEV-DTC-DC',
        'dbname'    => 'EXP_FU',
        'user'      => 'intra1',
        'password'  => 'intra1',
    ),
);
/****************************************************/




/**
 * TWIG
 ****************************************************/
$app['twig.configuration'] = array(
    'twig.options'        => array(
        'strict_variables' => true,
    ),
    'twig.path'           => array(__DIR__ . '/../../template')
);
/****************************************************/




/**
 * Configuration spécifique de l'application
 ****************************************************/
// Activation du mode débogage
$app['debug'] = false;

// Root prefix
$app['appRootPrefix'] = '/';
/****************************************************/