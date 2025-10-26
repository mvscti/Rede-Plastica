<?php

use App\Domain\Usuario\InformacoesContato\Telefone\TipoTelefoneContato;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaFisicaEntity;
use App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\TelefoneContatoEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use App\Infrastructure\Persistence\Entities\Usuario\VisitanteEntity;

$entities = [PessoaEntity::class, PessoaFisicaEntity::class, TelefoneContatoEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('telefones', [
    'Contato 1 (Telefone Móvel WhatsApp)' => [
        'data' => [
            'telefone' => '5531987654321',
            'isWhatsapp' => true,
            'tipo' => TipoTelefoneContato::MOVEL_RESIDENCIAL->value
        ]
    ],
    'Contato 2 (Telefone Móvel Não WhatsApp)' => [
        'data' => [
            'telefone' => '5532987654321',
            'isWhatsapp' => false,
            'tipo' => TipoTelefoneContato::MOVEL_RESIDENCIAL->value
        ]
    ],
    'Contato 3 (Telefone Fixo)' => [
        'data' => [
            'telefone' => '553212345678',
            'isWhatsapp' => false,
            'tipo' => TipoTelefoneContato::FIXO_COMERCIAL->value
        ]
    ],
]);

dataset('update data', [
    'Contato 1 (Atualizando Número)' => [
        'updateData' => [
            'telefone' => '5531987654322',
            'isWhatsapp' => true,
        ]
    ],
    'Contato 2 (Atualizando Para WhatsApp)' => [
        'updateData' => [
            'telefone' => '5532987654321',
            'isWhatsapp' => true,
        ]
    ],
    'Contato 3 (Atualizar Telefone Fixo)' => [
        'updateData' => [
            'telefone' => '553412345678',
            'isWhatsapp' => false,
        ]
    ],
]);

function createTelefoneContatoEntity(?string $telefone, bool $isWhatsapp, string $tipo, UsuarioEntity $usuarioEntity): TelefoneContatoEntity
{
    $telefoneContato = new TelefoneContatoEntity();
    $telefoneContato->telefone = $telefone;
    $telefoneContato->isWhatsapp = $isWhatsapp;
    $telefoneContato->tipo = TipoTelefoneContato::from($tipo);
    $telefoneContato->usuario = $usuarioEntity;
    return $telefoneContato;
}

it('deve associar TelefoneContatoEntity ao UsuarioEntity, persistir e recuperar', function (array $data) {
    [$telefone, $isWhatsapp, $tipo] = array_values($data);

    $usuario = $this->createAndPersistMinimalVisitanteEntity();
    $telefoneContato = createTelefoneContatoEntity($telefone, $isWhatsapp, $tipo, $usuario);
    $usuario->telefones[] = $telefoneContato;

    $foundUsuario = $this->persistAndFlushWithFind($usuario, withClear: false);

    expect($foundUsuario->telefones)->not->toBeEmpty()
        ->and($foundUsuario->telefones)->toHaveCount(1)
        ->and($foundTelefone = $foundUsuario->telefones->first())
            ->and($foundTelefone)->toBeInstanceOf(TelefoneContatoEntity::class)
            ->and($foundTelefone->telefone)->toBe($telefone)
            ->and($foundTelefone->isWhatsapp)->toBe($isWhatsapp)
            ->and($foundTelefone->tipo->value)->toBe($tipo);
})->with('telefones');

it('deve associar múltiplos TelefoneContatoEntity a um UsuarioEntity, persistir e recuperar', function (array $data) {
    $telefones = $data;

    $usuario = $this->createAndPersistMinimalVisitanteEntity();
    foreach ($telefones as $telefoneData) {
        $telefoneContato = createTelefoneContatoEntity(
            ...array_values(array_merge($telefoneData, ['usuario' => $usuario]))
        );
        $usuario->telefones[] = $telefoneContato;
    }

    $foundUsuario = $this->persistAndFlushWithFind($usuario);

    expect($foundUsuario->telefones)->not->toBeEmpty()
        ->and($foundUsuario->telefones)->toHaveSameSize($telefones)
        ->and($foundUsuario->telefones)->toContainOnlyInstancesOf(TelefoneContatoEntity::class)
        ->and($foundUsuario->telefones)->each(function ($telefoneContato, $index) use ($telefones) {
            $telefoneContato->telefone->toBe($telefones[$index]['telefone']);
            $telefoneContato->isWhatsapp->toBe($telefones[$index]['isWhatsapp']);
            $telefoneContato->tipo->value->toBe($telefones[$index]['tipo']);
        });
})->with([
    'Contatos Múltiplos' => [
        'data' => [
            [
                'telefone' => '5531987654321',
                'isWhatsapp' => true,
                'tipo' => TipoTelefoneContato::MOVEL_RESIDENCIAL->value,
            ],
            [
                'telefone' => '5532987654321',
                'isWhatsapp' => false,
                'tipo' => TipoTelefoneContato::MOVEL_RESIDENCIAL->value,
            ],
            [
                'telefone' => '553212345678',
                'isWhatsapp' => false,
                'tipo' => TipoTelefoneContato::FIXO_COMERCIAL->value,
            ],
        ]
    ]
]);

it('deve atualizar os dados de TelefoneContatoEntity', function (array $data, array $updateData) {
    $usuario = $this->createAndPersistMinimalVisitanteEntity();
    $telefoneContato = createTelefoneContatoEntity(...array_values(array_merge($data, ['usuario' => $usuario])));
    $usuario->telefones[] = $telefoneContato;

    $this->persistAndFlush($usuario);

    [$newTelefone, $newIsWhatsapp] = array_values($updateData);

    $telefoneContato->telefone = $newTelefone;
    $telefoneContato->isWhatsapp = $newIsWhatsapp;

    $found = $this->persistAndFlushWithFind($telefoneContato, withClear: false);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($found->usuario->id);

    expect($found)->toBeInstanceOf(TelefoneContatoEntity::class)
        ->and($found->id)->toBe($telefoneContato->id)
        ->and($found->telefone)->toBe($newTelefone)
        ->and($found->isWhatsapp)->toBe($newIsWhatsapp)
        ->and($foundUsuario->telefones)->not->toBeEmpty()
        ->and($foundUsuario->telefones)->toHaveCount(1)
        ->and($foundTelefone = $foundUsuario->telefones->first())
            ->and($foundTelefone)->toBeInstanceOf(TelefoneContatoEntity::class)
            ->and($foundTelefone->id)->toBe($telefoneContato->id)
            ->and($foundTelefone->telefone)->toBe($newTelefone)
            ->and($foundTelefone->isWhatsapp)->toBe($newIsWhatsapp);
})->with('telefones', 'update data');

it('deve remover TelefoneContatoEntity', function (array $data) {
    $usuario = $this->createAndPersistMinimalVisitanteEntity();
    $telefoneContato = createTelefoneContatoEntity(...array_values(array_merge($data, ['usuario' => $usuario])));
    $usuario->telefones[] = $telefoneContato;

    $this->persistAndFlush($usuario);
    $id = $telefoneContato->id;
    $usuarioId = $usuario->id;

    $this->removeAndFlush($telefoneContato);
    $found = $this->em->getRepository(TelefoneContatoEntity::class)->find($id);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($usuarioId);

    expect($found)->toBeNull()
        ->and($foundUsuario->telefones)->toBeEmpty();
})->with('telefones');

it('deve remover TelefoneContatoEntity ao remover UsuarioEntity', function (array $data) {
    $usuario = $this->createAndPersistMinimalVisitanteEntity();
    $telefoneContato = createTelefoneContatoEntity(...array_values(array_merge($data, ['usuario' => $usuario])));
    $usuario->telefones[] = $telefoneContato;

    $this->persistAndFlush($usuario);
    $id = $telefoneContato->id;
    $usuarioId = $usuario->id;

    $this->removeAndFlush($usuario);
    $found = $this->em->getRepository(TelefoneContatoEntity::class)->find($id);
    $foundUsuario = $this->em->getRepository(UsuarioEntity::class)->find($usuarioId);

    expect($found)->toBeNull()
        ->and($foundUsuario)->toBeNull();
})->with('telefones');