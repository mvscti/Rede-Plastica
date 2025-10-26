<?php

use App\Domain\Residuo\Plastico\ClassificacaoPlasticoNbr13230;
use App\Domain\Residuo\Residuo\ClasseResiduoNbr10004;
use App\Infrastructure\Persistence\Entities\Residuo\Imagem\ImagemEntity;
use App\Infrastructure\Persistence\Entities\Residuo\PlasticoEntity;
use App\Infrastructure\Persistence\Entities\Residuo\ResiduoEntity;

$entities = [ResiduoEntity::class, PlasticoEntity::class, ImagemEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('imagens', [
    'Imagem 1' => [
        'data' => [
            'url' => 'https://example.com/imagem1.jpg',
        ]
    ],
    'Imagem 2' => [
        'data' => [
            'url' => 'https://example.com/imagem2.png',
        ]
    ]
]);

dataset('update data', [
    'Imagem 1' => [
        'updateData' => [
            'url' => 'https://example.com/imagem1_updated.jpg',
        ]
    ],
    'Imagem 2' => [
        'updateData' => [
            'url' => 'https://example.com/imagem2_updated.png',
        ]
    ],
]);

function createImagemEntity(string $url, ResiduoEntity $residuoEntity): ImagemEntity
{
    $imagem = new ImagemEntity();
    $imagem->url = $url;
    $imagem->residuo = $residuoEntity;
    return $imagem;
}

function createAndPersistMinimalPlasticoEntity(): ResiduoEntity
{
    $plastico = new PlasticoEntity();
    $plastico->classificacaoNbr13230 = ClassificacaoPlasticoNbr13230::PET;
    $plastico->cor = 'Azul';
    $plastico->classeNbr10004 = ClasseResiduoNbr10004::CLASSE_II;
    $plastico->preco = 10.50;
    $plastico->quantidade = 100;

    test()->persistAndFlush($plastico);
    return $plastico;
}

it('deve associar ImagemEntity ao ResiduoEntity, persistir e recuperar', function (array $data) {
    $url = $data['url'];

    $residuo = createAndPersistMinimalPlasticoEntity();
    $imagem = createImagemEntity($url, $residuo);
    $residuo->imagens[] = $imagem;

    $foundResiduo = $this->persistAndFlushWithFind($residuo);

    expect($foundResiduo->imagens)->not->toBeEmpty()
        ->and($foundResiduo->imagens)->toHaveCount(1)
        ->and($foundImagem = $foundResiduo->imagens->first())
            ->and($foundImagem)->toBeInstanceOf(ImagemEntity::class)
            ->and($foundImagem->url)->toBe($url);
})->with('imagens');

it('deve associar múltiplas ImagemEntity a um ResiduoEntity, persistir e recuperar', function (array $data) {
    $imagens = $data;

    $residuo = createAndPersistMinimalPlasticoEntity();
    foreach ($imagens as $imagemData) {
        $imagem = createImagemEntity(...array_values(array_merge($imagemData, ['residuo' => $residuo])));
        $residuo->imagens[] = $imagem;
    }

    $foundResiduo = $this->persistAndFlushWithFind($residuo);

    expect($foundResiduo->imagens)->not->toBeEmpty()
        ->and($foundResiduo->imagens)->toHaveSameSize($imagens)
        ->and($foundResiduo->imagens)->toContainOnlyInstancesOf(ImagemEntity::class)
        ->and($foundResiduo->imagens)->each(function ($imagem, $index) use ($imagens) {
            $imagem->url->toBe($imagens[$index]['url']);
        });
})->with([
    'Imagens Múltiplas' => [
        'data' => [
            [
                'url' => 'https://example.com/imagem1.jpg',
            ],
            [
                'url' => 'https://example.com/imagem2.png',
            ],
            [
                'url' => 'https://example.com/imagem3.gif',
            ],
        ]
    ]
]);

it('deve atualizar os dados de ImagemEntity', function (array $data, array $updateData) {
    $residuo = createAndPersistMinimalPlasticoEntity();
    $imagem = createImagemEntity(...array_values(array_merge($data, ['residuo' => $residuo])));
    $residuo->imagens[] = $imagem;

    $this->persistAndFlush($residuo);

    $newUrl = $updateData['url'];

    $imagem->url = $newUrl;
    $this->persistAndFlush($imagem, true);

    $found = $this->em->getRepository(ImagemEntity::class)->find($imagem->id);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($found->residuo->id);

    expect($found)->toBeInstanceOf(ImagemEntity::class)
        ->and($found->id)->toBe($imagem->id)
        ->and($found->url)->toBe($newUrl)
        ->and($foundResiduo->imagens)->not->toBeEmpty()
        ->and($foundResiduo->imagens)->toHaveCount(1)
        ->and($foundImagem = $foundResiduo->imagens->first())
            ->and($foundImagem)->toBeInstanceOf(ImagemEntity::class)
            ->and($foundImagem->id)->toBe($imagem->id)
            ->and($foundImagem->url)->toBe($newUrl);
})->with('imagens', 'update data');

it('deve remover ImagemEntity', function (array $data) {
    $coletor = createAndPersistMinimalPlasticoEntity();
    $imagem = createImagemEntity(...array_values(array_merge($data, ['residuo' => $coletor])));
    $coletor->imagens[] = $imagem;

    $this->persistAndFlush($imagem);
    $id = $imagem->id;

    $this->removeAndFlush($imagem);
    $found = $this->em->getRepository(ImagemEntity::class)->find($id);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($coletor->id);

    expect($found)->toBeNull()
        ->and($foundResiduo->imagens)->toBeEmpty();
})->with('imagens');

it('deve remover ImagemEntity ao remover ResiduoEntity', function (array $data) {
    $coletor = createAndPersistMinimalPlasticoEntity();
    $imagem = createImagemEntity(...array_values(array_merge($data, ['residuo' => $coletor])));
    $coletor->imagens[] = $imagem;

    $this->persistAndFlush($imagem);
    $id = $imagem->id;
    $coletorId = $coletor->id;

    $this->removeAndFlush($coletor);
    $found = $this->em->getRepository(ImagemEntity::class)->find($id);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($coletorId);

    expect($found)->toBeNull()
        ->and($foundResiduo)->toBeNull();
})->with('imagens');