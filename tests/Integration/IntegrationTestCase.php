<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Container\ContainerInterface;

abstract class IntegrationTestCase extends BaseTestCase
{
    public static function getContainer(): ContainerInterface
    {
        /** @var ContainerInterface $container */
        $container = require dirname(__DIR__, 2) . '/config/bootstrap.php';
        return $container;
    }
}
