<?php

declare(strict_types=1);

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Infrastructure\Http\Middlewares\HttpExceptionHandlerMiddleware;
use App\Infrastructure\Providers\PasswordHasherProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

use function DI\autowire;

return [
    // Infrastructure Core
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

    // HTTP
    App::class => function (ContainerInterface $container) {
        $displayErrors = $container->get('config')['app']['display_errors'] ?? false;

        $app = AppFactory::create(container: $container);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();

        $app->add(new WhoopsMiddleware([
            'enable' => $displayErrors,
            'editor' => 'vscode',
            'title'  => 'Um erro ocorreu!',
        ]));

        $app->add(HttpExceptionHandlerMiddleware::class);

        return $app;
    },
    ResponseFactoryInterface::class => autowire(ResponseFactory::class),

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