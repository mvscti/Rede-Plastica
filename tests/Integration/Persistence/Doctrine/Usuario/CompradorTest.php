<?php

use App\Infrastructure\Persistence\Entities\Usuario\CompradorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;

$entities = [UsuarioEntity::class, CompradorEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('compradores', [
    'Comprador 1' => [
        'data' => [
            'login' => 'comprador1@teste.com',
            'senha' => 'compradorSenha1',
            'deseja_recomendacao' => true
        ]
    ],
    'Comprador 2' => [
        'data' => [
            'login' => 'comprador2@teste.com',
            'senha' => 'compradorSenha2',
            'deseja_recomendacao' => false
        ]
    ],
]);

dataset('update data', [
    'Comprador 1' => [
        'updateData' => [
            'login' => 'comprador1@atualizado.com',
            'senha' => 'compradorSenhaAtualizada1',
            'deseja_recomendacao' => false
        ]
    ],
    'Comprador 2' => [
        'updateData' => [
            'login' => 'comprador2@atualizado.com',
            'senha' => 'compradorSenhaAtualizada2',
            'deseja_recomendacao' => true
        ]
    ],
]);

function createCompradorEntity(string $login, string $senha, bool $desejaRecomendacao): CompradorEntity
{
    $comprador = new CompradorEntity();
    $comprador->login = $login;
    $comprador->senha = password_hash($senha, PASSWORD_DEFAULT);
    $comprador->desejaRecomendacao = $desejaRecomendacao;
    $comprador->pessoa = test()->createMinimalPessoaFisicaEntity();
    return $comprador;
}

it('deve persistir e recuperar CompradorEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data
) {
    [$login, $senha, $desejaRecomendacao] = array_values($data);
    $comprador = createCompradorEntity($login, $senha, $desejaRecomendacao);

    $found = $this->persistAndFlushWithFind($comprador);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(CompradorEntity::class)
        ->and($found->id)->toBe($comprador->id)
        ->and($found->login)->toBe($login)
        ->and(password_verify($senha, $found->senha))->toBeTrue()
        ->and($found->desejaRecomendacao)->toBe($desejaRecomendacao)
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($login)
        ->and(password_verify($senha, $foundUsuario->senha))->toBeTrue();
})->with('compradores');

it('deve atualizar CompradorEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $comprador = createCompradorEntity(...array_values($data));
    $this->persistAndFlush($comprador);

    [$newLogin, $newSenha, $newDesejaRecomendacao] = array_values($updateData);
    $comprador->login = $newLogin;
    $comprador->senha = password_hash($newSenha, PASSWORD_DEFAULT);
    $comprador->desejaRecomendacao = $newDesejaRecomendacao;

    $found = $this->persistAndFlushWithFind($comprador);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(CompradorEntity::class)
        ->and($found->id)->toBe($comprador->id)
        ->and($found->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $found->senha))->toBeTrue()
        ->and($found->desejaRecomendacao)->toBe($newDesejaRecomendacao)
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $foundUsuario->senha))->toBeTrue();
})->with('compradores', 'update data');

it('deve remover CompradorEntity e UsuarioEntity (entidade/tabela base)', function (array $data) {
    $comprador = createCompradorEntity(...array_values($data));
    $this->persistAndFlush($comprador);
    $id = $comprador->id;

    $this->removeAndFlush($comprador);
    $found = $this->em->getRepository(CompradorEntity::class)->find($id);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundUsuario)->toBeNull();
})->with('compradores');
