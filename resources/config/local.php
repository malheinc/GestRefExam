<?php



/**
 * Heritage de la configuration
 ****************************************************/
require __DIR__ . '/dev.php';

$app['environnement'] = 'LOCAL';
/****************************************************/




/**
 * Doctrine
 ****************************************************/
$app['dbs.options'] = array(
    'Stage_db' => array(
        'driver' => 'pdo_Mysql',
        'port'      => '',
        'host'      => '',
        'dbname'    => 'gestRef',
        'user'      => 'root',
        'password'  => 'joliverie',
    ),
);
/****************************************************/




/**
 * Configuration spécifique de l'application
 ****************************************************/
// Root prefix
$app['appRootPrefix'] = '/gestref/web/';
/****************************************************/
