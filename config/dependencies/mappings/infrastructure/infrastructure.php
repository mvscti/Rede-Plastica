<?php

declare(strict_types=1);

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Infrastructure\Http\Middlewares\HttpExceptionHandlerMiddleware;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CepType;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CnpjType;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CpfType;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\PointType;
use App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\CustomTypes\TelefoneE164Type;
use App\Infrastructure\Providers\PasswordHasherProvider;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
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
        $appEnv = $config['app']['env'];

        $doctrineConfig = ORMSetup::createAttributeMetadataConfiguration(
            paths: [dirname(__DIR__, 3) . '/src/Infrastructure/Persistence/Entities'],
            isDevMode: $appEnv !== 'production'
        );

        $connection = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $dbConfig['host'],
            'user'     => $dbConfig['user'],
            'password' => $dbConfig['password'],
            'dbname'   => $dbConfig['database'],
            'charset' => 'utf8',
        ], $doctrineConfig);

        $entityManager = new EntityManager($connection, $doctrineConfig);

        $registerCustomDoctrineType = function (
            string $doctrineTypeName,
            string $doctrineTypeClass
        ) use ($entityManager): void
        {
            if (!Type::hasType($doctrineTypeName)) {
                Type::addType($doctrineTypeName, $doctrineTypeClass);

                $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping(
                    "db_$doctrineTypeName",
                    $doctrineTypeName
                );
            }
        };

        // Registrando tipos customizados do Doctrine
        $registerCustomDoctrineType(CepType::CEP, CepType::class);
        $registerCustomDoctrineType(PointType::POINT, PointType::class);
        $registerCustomDoctrineType(CpfType::CPF, CpfType::class);
        $registerCustomDoctrineType(CnpjType::CNPJ, CnpjType::class);
        $registerCustomDoctrineType(TelefoneE164Type::TELEFONE, TelefoneE164Type::class);

        return $entityManager;
    },

    // Providers
    PasswordHasherProviderInterface::class => autowire(PasswordHasherProvider::class),
];