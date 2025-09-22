<?php

declare(strict_types=1);

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Infrastructure\Http\Controllers\HttpExceptions\MethodNotAllowedExceptionController;
use App\Infrastructure\Http\Controllers\HttpExceptions\NotFoundExceptionController;
use App\Infrastructure\Providers\PasswordHasherProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;

use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function DI\autowire;

return [
    // HTTP
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
    ValidatorInterface::class => function () {
        $translator = new Translator('pt_BR');
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addResource(
            'php',
            TRANSLATIONS_DIR . '/validators.pt_BR.php',
            'pt_BR',
            'validators'
        );

        return Validation::createValidatorBuilder()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();
    },

    // Persistence
    EntityManagerInterface::class => function (ContainerInterface $container) {
        $config = $container->get('config');
        $dbConfig = $config['db'];

        $doctrineConfig = ORMSetup::createAttributeMetadataConfiguration(
            paths: [dirname(__DIR__, 3) . '/src/Infrastructure/Persistence/Entities'],
            isDevMode: $config['app']['env'] !== 'production'
        );

        $connection = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $dbConfig['host'],
            'user'     => $dbConfig['user'],
            'password' => $dbConfig['password'],
            'dbname'   => $dbConfig['database'],
            'charset' => 'utf8',
        ], $doctrineConfig);

        return new EntityManager($connection, $doctrineConfig);
    },

    // Providers
    PasswordHasherProviderInterface::class => autowire(PasswordHasherProvider::class),
];