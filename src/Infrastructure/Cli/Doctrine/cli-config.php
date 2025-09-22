<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$container = require dirname(__DIR__, 4) . '/config/bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);

$commands = [
];

ConsoleRunner::run(
    new SingleManagerProvider($entityManager),
    $commands
);