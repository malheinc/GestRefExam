<?php

use Monolog\Logger;




/**
 * Heritage de la configuration
 ****************************************************/
require __DIR__ . '/prod.php';

$app['environnement'] = 'INT';
/****************************************************/




/**
 * Monologue
 ****************************************************/
$app['monolog.level'] = Logger::WARNING;
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