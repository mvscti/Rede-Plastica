<?php

use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use App\Infrastructure\Persistence\Entities\Usuario\VisitanteEntity;

$entities = [UsuarioEntity::class, VisitanteEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('visitantes', [
    'Visitante 1' => [
        'data' => [
            'login' => 'visitante1@teste.com',
            'senha' => 'visitanteSenha1'
        ]
    ],
    'Visitante 2' => [
        'data' => [
            'login' => 'visitante2@teste.com',
            'senha' => 'visitanteSenha2'
        ]
    ],
]);

dataset('update data', [
    'Visitante 1' => [
        'updateData' => [
            'login' => 'visitante1@atualizado.com',
            'senha' => 'visitanteSenhaAtualizada1'
        ]
    ],
    'Visitante 2' => [
        'updateData' => [
            'login' => 'visitante2@atualizado.com',
            'senha' => 'visitanteSenhaAtualizada2'
        ]
    ],
]);

it('deve persistir e recuperar VisitanteEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data
) {
    [$login, $senha] = array_values($data);
    $visitante = $this->createVisitanteEntity($login, $senha);

    $found = $this->persistAndFlushWithFind($visitante);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(VisitanteEntity::class)
        ->and($found->id)->toBe($visitante->id)
        ->and($found->login)->toBe($login)
        ->and(password_verify($senha, $found->senha))->toBeTrue()
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($login)
        ->and(password_verify($senha, $foundUsuario->senha))->toBeTrue();
})->with('visitantes');

it('deve atualizar VisitanteEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $visitante = $this->createVisitanteEntity(...array_values($data));
    $this->persistAndFlush($visitante);

    [$newLogin, $newSenha] = array_values($updateData);
    $visitante->login = $newLogin;
    $visitante->senha = password_hash($newSenha, PASSWORD_DEFAULT);

    $found = $this->persistAndFlushWithFind($visitante);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(VisitanteEntity::class)
        ->and($found->id)->toBe($visitante->id)
        ->and($found->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $found->senha))->toBeTrue()
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $foundUsuario->senha))->toBeTrue();
})->with('visitantes', 'update data');

it('deve remover VisitanteEntity e UsuarioEntity (entidade/tabela base)', function (array $data) {
    $visitante = $this->createVisitanteEntity(...array_values($data));
    $this->persistAndFlush($visitante);
    $id = $visitante->id;

    $this->removeAndFlush($visitante);
    $found = $this->em->getRepository(VisitanteEntity::class)->find($id);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundUsuario)->toBeNull();
})->with('visitantes');
