<?php

use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

$entities = [UsuarioEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

it('deve lançar exceção ao persistir dois objetos UsuarioEntity com login igual', function (
    array $usuarioData1, array $usuarioData2
) {
    $usuario1 = $this->createVisitanteEntity(...array_values($usuarioData1));
    $usuario2 = $this->createVisitanteEntity(...array_values($usuarioData2));

    $this->persistAndFlush($usuario1);
    $this->expectException(UniqueConstraintViolationException::class);
    $this->persistAndFlush($usuario2);
})->with([
    'Usuários com login igual' => [
        [
            'login' => 'usuario@teste.com',
            'senha' => 'senhaForte1',
        ],
        [
            'login' => 'usuario@teste.com',
            'senha' => 'senhaForte2',
        ],
    ]
]);