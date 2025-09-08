<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

date_default_timezone_set('America/Sao_Paulo');

$dotenv = Dotenv::createImmutable(dirname(__DIR__) . '/', $_SERVER['ENV_FILE'] ?? '.env');
$dotenv->safeLoad();
