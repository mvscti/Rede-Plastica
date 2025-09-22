<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Slim\App;

/** @var ContainerInterface $container */
$container = require dirname(__DIR__) . '/config/bootstrap.php';

/** @var App $app */
$app = $container->get(App::class);

(require_once dirname(__DIR__) . '/routes/routes.php')($app);

$app->run();