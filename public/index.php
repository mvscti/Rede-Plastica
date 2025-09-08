<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/bootstrap.php';

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;

$containerDefinitions = require dirname(__DIR__) . '/config/dependencies/dependencies.php';

$containerBuilder = new ContainerBuilder()->addDefinitions($containerDefinitions);

if ($containerDefinitions['config']['app']['env'] === 'production') {
    $containerBuilder->enableCompilation(dirname(__DIR__) . '/storage/tmp/di');
    $containerBuilder->writeProxiesToFile(true, dirname(__DIR__) . '/storage/tmp/di/proxies');
}

/** @var ContainerInterface $container */
$container = $containerBuilder->build();

/** @var App $app */
$app = $container->get(App::class);

(require_once dirname(__DIR__) . '/routes/routes.php')($app);

$app->run();