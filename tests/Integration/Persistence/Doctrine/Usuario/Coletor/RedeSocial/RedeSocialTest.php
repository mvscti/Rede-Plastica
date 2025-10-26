<?php

use App\Domain\RedeSocial\TipoRedeSocial;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaFisicaEntity;
use App\Infrastructure\Persistence\Entities\Usuario\Coletor\ColetorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\Coletor\RedeSocial\RedeSocialEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;

$entities = [
    PessoaEntity::class, PessoaFisicaEntity::class, UsuarioEntity::class, ColetorEntity::class, RedeSocialEntity::class
];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('redes sociais', [
    'Rede Social 1 (Com Todos os Campos)' => [
        'data' => [
            'tipo' => TipoRedeSocial::FACEBOOK->value,
            'profile' => 'https://www.facebook.com/usuario',
        ]
    ],
    'Rede Social 2 (Com Nome e URL Diferentes)' => [
        'data' => [
            'tipo' => TipoRedeSocial::X->value,
            'profile' => 'https://www.twitter.com/usuario',
        ]
    ],
    'Rede Social 3 (Com Nome e URL Diferentes)' => [
        'data' => [
            'tipo' => TipoRedeSocial::LINKEDIN->value,
            'profile' => 'https://www.linkedin.com/in/usuario',
        ]
    ],
]);

dataset('update data', [
    'Rede Social 1' => [
        'updateData' => [
            'tipo' => 'instagram',
            'profile' => 'https://www.instagram.com/usuario_atualizado',
        ]
    ],
    'Rede Social 2' => [
        'updateData' => [
            'tipo' => 'facebook',
            'profile' => 'https://www.facebook.com/usuario_novo',
        ]
    ],
    'Rede Social 3' => [
        'updateData' => [
            'tipo' => 'x',
            'profile' => 'https://www.x.com/usuario_novo',
        ]
    ],
]);

function createRedeSocialEntity(string $tipo, string $profile, ColetorEntity $coletorEntity): RedeSocialEntity
{
    $redeSocial = new RedeSocialEntity();
    $redeSocial->tipo = TipoRedeSocial::from($tipo);
    $redeSocial->profile = $profile;
    $redeSocial->coletor = $coletorEntity;
    return $redeSocial;
}

it('deve associar RedeSocialEntity ao ColetorEntity, persistir e recuperar', function (array $data) {
    [$tipo, $profile] = array_values($data);

    $coletor = $this->createAndPersistMinimalColetorEntity();
    $redeSocial = createRedeSocialEntity($tipo, $profile, $coletor);
    $coletor->redesSociais[] = $redeSocial;

    $foundColetor = $this->persistAndFlushWithFind($coletor);

    expect($foundColetor->redesSociais)->not->toBeEmpty()
        ->and($foundColetor->redesSociais)->toHaveCount(1)
        ->and($foundRedeSocial = $foundColetor->redesSociais->first())
            ->and($foundRedeSocial)->toBeInstanceOf(RedeSocialEntity::class)
            ->and($foundRedeSocial->tipo->value)->toBe($tipo)
            ->and($foundRedeSocial->profile)->toBe($profile);
})->with('redes sociais');

it('deve associar múltiplas RedeSocialEntity a um ColetorEntity, persistir e recuperar', function (array $data) {
    $redesSociais = $data;

    $coletor = $this->createAndPersistMinimalColetorEntity();
    foreach ($redesSociais as $redesSocialData) {
        $redesSocial = createRedeSocialEntity(...array_values(array_merge($redesSocialData, ['coletor' => $coletor])));
        $coletor->redesSociais[] = $redesSocial;
    }

    $foundColetor = $this->persistAndFlushWithFind($coletor);

    expect($foundColetor->redesSociais)->not->toBeEmpty()
        ->and($foundColetor->redesSociais)->toHaveSameSize($redesSociais)
        ->and($foundColetor->redesSociais)->toContainOnlyInstancesOf(RedeSocialEntity::class)
        ->and($foundColetor->redesSociais)->each(function ($redeSocial, $index) use ($redesSociais) {
            $redeSocial->tipo->value->toBe($redesSociais[$index]['tipo']);
            $redeSocial->profile->toBe($redesSociais[$index]['profile']);
        });
})->with([
    'Múltiplas Redes Sociais' => [
        'data' => [
            [ 'tipo' => 'instagram', 'profile' => 'https://www.instagram.com/multipla'],
            [ 'tipo' => 'facebook', 'profile' => 'https://www.facebook.com/multipla'],
            [ 'tipo' => 'x', 'profile' => 'https://www.x.com/multipla']
        ]
    ]
]);

it('deve atualizar os dados de RedeSocialEntity', function (array $data, array $updateData) {
    $coletor = $this->createAndPersistMinimalColetorEntity();
    $redeSocial = createRedeSocialEntity(...array_values(array_merge($data, ['coletor' => $coletor])));
    $coletor->redesSociais[] = $redeSocial;

    $this->persistAndFlush($coletor);

    [, $newProfile] = array_values($updateData);

    $redeSocial->profile = $newProfile;

    $found = $this->persistAndFlushWithFind($redeSocial, withClear: false);
    $foundColetor = $this->em->getRepository(ColetorEntity::class)->find($found->coletor->id);

    expect($found)->toBeInstanceOf(RedeSocialEntity::class)
        ->and($found->id)->toBe($redeSocial->id)
        ->and($found->profile)->toBe($newProfile)
        ->and($foundColetor->redesSociais)->not->toBeEmpty()
        ->and($foundColetor->redesSociais)->toHaveCount(1)
        ->and($foundRedeSocial = $foundColetor->redesSociais->first())
            ->and($foundRedeSocial)->toBeInstanceOf(RedeSocialEntity::class)
            ->and($foundRedeSocial->id)->toBe($redeSocial->id)
            ->and($foundRedeSocial->profile)->toBe($newProfile);
})->with('redes sociais', 'update data');

it('deve remover RedeSocialEntity', function (array $data) {
    $coletor = $this->createAndPersistMinimalColetorEntity();
    $redeSocial = createRedeSocialEntity(...array_values(array_merge($data, ['coletor' => $coletor])));
    $coletor->redesSociais[] = $redeSocial;

    $this->persistAndFlush($coletor);
    $redeSocialId = $redeSocial->id;
    $coletorId = $coletor->id;

    $this->removeAndFlush($redeSocial);
    $found = $this->em->getRepository(RedeSocialEntity::class)->find($redeSocialId);
    $foundColetor = $this->em->getRepository(ColetorEntity::class)->find($coletorId);

    expect($found)->toBeNull()
        ->and($foundColetor->redesSociais)->toBeEmpty();
})->with('redes sociais');

it('deve remover RedeSocialEntity ao remover ColetorEntity', function (array $data) {
    $coletor = $this->createAndPersistMinimalColetorEntity();
    $redeSocial = createRedeSocialEntity(...array_values(array_merge($data, ['coletor' => $coletor])));
    $coletor->redesSociais[] = $redeSocial;

    $this->persistAndFlush($coletor);
    $redeSocialId = $redeSocial->id;
    $coletorId = $coletor->id;

    $this->removeAndFlush($coletor);
    $found = $this->em->getRepository(RedeSocialEntity::class)->find($redeSocialId);
    $foundColetor = $this->em->getRepository(ColetorEntity::class)->find($coletorId);

    expect($found)->toBeNull()
        ->and($foundColetor)->toBeNull();
})->with('redes sociais');
