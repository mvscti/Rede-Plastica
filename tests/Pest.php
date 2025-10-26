<?php

use Tests\Integration\IntegrationTestCase;
use Tests\Integration\Persistence\Doctrine\DoctrineTestCase;
use Tests\Integration\Persistence\Doctrine\Pessoa\PessoaTestHelpersTrait;
use Tests\Integration\Persistence\Doctrine\Usuario\UsuarioTestHelpersTrait;

// TESTES DE INTEGRAÇÃO
//pest()->extend(IntegrationTestCase::class)->in('Integration');

pest()->extend(DoctrineTestCase::class)->in('Integration/Persistence/Doctrine');

uses(PessoaTestHelpersTrait::class)->in(
    'Integration/Persistence/Doctrine/Pessoa',
    'Integration/Persistence/Doctrine/Usuario'
);

uses(UsuarioTestHelpersTrait::class)->in(
    'Integration/Persistence/Doctrine/Usuario'
);

//    beforeEach(function () {
//        $schemaTool = new SchemaTool($this->em);
//        $classes = $this->em->getMetadataFactory()->getAllMetadata();
//        $schemaTool->createSchema($classes);
//    });