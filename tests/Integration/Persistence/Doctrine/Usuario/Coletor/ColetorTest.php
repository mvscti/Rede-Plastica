<?php

use App\Infrastructure\Persistence\Entities\Usuario\Coletor\ColetorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;

$entities = [UsuarioEntity::class, ColetorEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('coletores', [
    'Coletor 1' => [
        'data' => [
            'login' => 'coletor1@teste.com',
            'senha' => 'coletorSenha1'
        ]
    ],
    'Coletor 2' => [
        'data' => [
            'login' => 'coletor2@teste.com',
            'senha' => 'coletorSenha2'
        ]
    ],
]);

dataset('update data', [
    'Coletor 1' => [
        'updateData' => [
            'login' => 'coletor1@atualizado.com',
            'senha' => 'coletorSenhaAtualizada1'
        ]
    ],
    'Coletor 2' => [
        'updateData' => [
            'login' => 'coletor2@atualizado.com',
            'senha' => 'coletorSenhaAtualizada2'
        ]
    ],
]);

function createColetorEntity(string $login, string $senha): ColetorEntity
{
    $coletor = new ColetorEntity();
    $coletor->login = $login;
    $coletor->senha = password_hash($senha, PASSWORD_DEFAULT);
    $coletor->pessoa = test()->createMinimalPessoaFisicaEntity();
    return $coletor;
}

it('deve persistir e recuperar ColetorEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data
) {
    [$login, $senha] = array_values($data);
    $coletor = createColetorEntity($login, $senha);

    $found = $this->persistAndFlushWithFind($coletor);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(ColetorEntity::class)
        ->and($found->id)->toBe($coletor->id)
        ->and($found->login)->toBe($login)
        ->and(password_verify($senha, $found->senha))->toBeTrue()
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($login)
        ->and(password_verify($senha, $foundUsuario->senha))->toBeTrue();
})->with('coletores');

it('deve atualizar ColetorEntity e os dados comuns em UsuarioEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $coletor = createColetorEntity(...array_values($data));
    $this->persistAndFlush($coletor);

    [$newLogin, $newSenha] = array_values($updateData);
    $coletor->login = $newLogin;
    $coletor->senha = password_hash($newSenha, PASSWORD_DEFAULT);

    $found = $this->persistAndFlushWithFind($coletor);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(ColetorEntity::class)
        ->and($found->id)->toBe($coletor->id)
        ->and($found->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $found->senha))->toBeTrue()
        ->and($foundUsuario)->toBeInstanceOf(UsuarioEntity::class)
        ->and($foundUsuario->id)->toBe($found->id)
        ->and($foundUsuario->login)->toBe($newLogin)
        ->and(password_verify($newSenha, $foundUsuario->senha))->toBeTrue();
})->with('coletores', 'update data');

it('deve remover ColetorEntity e UsuarioEntity (entidade/tabela base)', function (array $data) {
    $coletor = createColetorEntity(...array_values($data));
    $this->persistAndFlush($coletor);
    $id = $coletor->id;

    $this->removeAndFlush($coletor);
    $found = $this->em->getRepository(ColetorEntity::class)->find($id);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundUsuario)->toBeNull();
})->with('coletores');
