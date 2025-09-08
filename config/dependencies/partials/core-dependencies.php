<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\Exceptions\MethodNotAllowedExceptionController;
use App\Infrastructure\Http\Controllers\Exceptions\NotFoundExceptionController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;

use function DI\autowire;

return [
    'config' => [
        'app' => [
            'name' => $_SERVER['APP_NAME'] ?? 'Rede Plástica',
            'env' => $_SERVER['APP_ENV'] ?? 'production',
            'base_url' => $_SERVER['APP_BASE_URL'],
        ],
        'slim' => [
            'display_error_details' => ($_SERVER['SLIM_DISPLAY_ERROR_DETAILS'] ?? 'false') === 'true',
            'log_errors' => ($_SERVER['SLIM_LOG_ERRORS'] ?? 'true') === 'true',
            'log_error_details' => ($_SERVER['SLIM_LOG_ERROR_DETAILS'] ?? 'true') === 'true',
        ],
        'log' => [
            'max_app_log_files' => (int)$_SERVER['MAX_APP_LOG_FILES'] ?? 30,
            'max_debug_log_files' => (int)$_SERVER['MAX_DEBUG_LOG_FILES'] ?? 7,
        ],
        'db' => [
            'host' => $_SERVER['DB_HOST'] ?? 'localhost',
            'database' => $_SERVER['DB_NAME'],
            'user' => $_SERVER['DB_USER'],
            'password' => $_SERVER['DB_PASSWORD']
        ],
    ],
    App::class => function (ContainerInterface $container) {
        $slimConfig = $container->get('config')['slim'];

        $app = AppFactory::create(container: $container);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();

        $errorMiddleware = $app->addErrorMiddleware(
            $slimConfig['display_error_details'],
            $slimConfig['log_errors'],
            $slimConfig['log_error_details']
        );

        $errorMiddleware->setErrorHandler(HttpNotFoundException::class, [NotFoundExceptionController::class, 'index']);
        $errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class, [MethodNotAllowedExceptionController::class, 'index']);

        return $app;
    },
    ResponseFactoryInterface::class => autowire(ResponseFactory::class),
];