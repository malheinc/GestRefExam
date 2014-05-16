<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__ . '/../resources/config/int.php';
require __DIR__ . '/../src/loader.php';

Symfony\Component\HttpFoundation\Request::enableHttpMethodParameterOverride();

$app->run();