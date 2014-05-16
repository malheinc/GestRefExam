<?php

use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;


$app->register(new SessionServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());


/*
 * TRANSLATION
 **********************************************************/
$app->register(new TranslationServiceProvider(), array(
    'locale_fallbacks' => array('fr'),
));
/**********************************************************/


/*
 * DOCTRINE
 **********************************************************/
$app->register(new Silex\Provider\DoctrineServiceProvider());
//$app['db']->executeQuery('SET DATEFORMAT "ymd"');
/**********************************************************/




/*
 * MONOLOG
 **********************************************************/
$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => $app['monolog.logfile'],
    'monolog.name' => 'app',
    'monolog.level' => $app['monolog.level'],
));
/**********************************************************/




/*
 * TWIG
 **********************************************************/
$app->register(new TwigServiceProvider(), $app['twig.configuration']); 
$app['twig']->addGlobal('appRootPrefix', $app['appRootPrefix']);
/**********************************************************/


return $app;
