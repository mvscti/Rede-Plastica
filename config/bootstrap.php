<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use DI\ContainerBuilder;
use Dotenv\Dotenv;

date_default_timezone_set('America/Sao_Paulo');

$dotenv = Dotenv::createImmutable(PROJECT_ROOT, $_SERVER['ENV_FILE'] ?? '.env');
$dotenv->safeLoad();

$containerDefinitions = require CONFIG_DIR . '/dependencies/all-dependencies.php';

$containerBuilder = new ContainerBuilder()->addDefinitions($containerDefinitions);

if ($containerDefinitions['config']['app']['env'] === 'production') {
    $containerBuilder->enableCompilation(TEMP_DIR . '/di');
    $containerBuilder->writeProxiesToFile(true, TEMP_DIR . '/di/proxies');
}

if ($containerDefinitions['config']['app']['display_errors'] === false) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

return $containerBuilder->build();