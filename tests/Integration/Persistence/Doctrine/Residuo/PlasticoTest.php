<?php

use App\Domain\Residuo\Plastico\ClassificacaoPlasticoNbr13230;
use App\Domain\Residuo\Residuo\ClasseResiduoNbr10004;
use App\Infrastructure\Persistence\Entities\Residuo\PlasticoEntity;
use App\Infrastructure\Persistence\Entities\Residuo\ResiduoEntity;

$entities = [ResiduoEntity::class, PlasticoEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('plasticos', [
    'Plástico PET Azul' => [
        'data' => [
            'classificacaoNbr' => ClassificacaoPlasticoNbr13230::PET->value,
            'cor' => 'Azul',
            'classeNbr' => ClasseResiduoNbr10004::CLASSE_II->value,
            'preco' => 10.50,
            'quantidade' => 100
        ]
    ],
    'Plástico PEAD Branco' => [
        'data'=> [
            'classificacaoNbr' => ClassificacaoPlasticoNbr13230::PEAD->value,
            'cor' => 'Branco',
            'classeNbr' => ClasseResiduoNbr10004::CLASSE_II->value,
            'preco' => 15.75,
            'quantidade' => 200
        ]
    ],
    'Plástico PVC Transparente' => [
        'data'=> [
            'classificacaoNbr' => ClassificacaoPlasticoNbr13230::PVC->value,
            'cor' => 'Transparente',
            'classeNbr' => ClasseResiduoNbr10004::CLASSE_II->value,
            'preco' => 8.30,
            'quantidade' => 150
        ]
    ]
]);

dataset('update data', [
    'Plástico PET Azul' => [
        'updateData' => [
            'preco' => 12.00,
            'quantidade' => 120
        ]
    ],
    'Plástico PEAD Branco' => [
        'updateData' => [
            'preco' => 18.00,
            'quantidade' => 220
        ]
    ],
    'Plástico PVC Transparente' => [
        'updateData' => [
            'preco' => 9.50,
            'quantidade' => 160
        ]
    ]
]);

function createPlasticoEntity(int $classificacaoNbr, string $cor, string $classeNbr, float $preco, int $quantidade): PlasticoEntity
{
    $plastico = new PlasticoEntity();
    $plastico->classificacaoNbr13230 = ClassificacaoPlasticoNbr13230::from($classificacaoNbr);
    $plastico->cor = $cor;
    $plastico->classeNbr10004 = ClasseResiduoNbr10004::from($classeNbr);
    $plastico->preco = $preco;
    $plastico->quantidade = $quantidade;
    return $plastico;
}

it('deve persistir e recuperar PlasticoEntity e os dados comuns em ResiduoEntity (entidade/tabela base)', function (
    array $data
) {
    [$classificacaoNbr, $cor, $classeNbr, $preco, $quantidade] = array_values($data);
    $plastico = createPlasticoEntity($classificacaoNbr, $cor, $classeNbr, $preco, $quantidade);

    $found = $this->persistAndFlushWithFind($plastico);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(PlasticoEntity::class)
        ->and($found->id)->toBe($plastico->id)
        ->and($found->classificacaoNbr13230->value)->toBe($classificacaoNbr)
        ->and($found->cor)->toBe($cor)
        ->and($found->classeNbr10004->value)->toBe($classeNbr)
        ->and($found->preco)->toBe($preco)
        ->and($found->quantidade)->toBe($quantidade)
        ->and($foundResiduo)->toBeInstanceOf(ResiduoEntity::class)
        ->and($foundResiduo->id)->toBe($found->id)
        ->and($foundResiduo->cor)->toBe($cor)
        ->and($foundResiduo->classeNbr10004->value)->toBe($classeNbr)
        ->and($foundResiduo->preco)->toBe($preco)
        ->and($foundResiduo->quantidade)->toBe($quantidade);
})->with('plasticos');

it('deve atualizar os dados de PlasticoEntity e os dados comuns em ResiduoEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $plastico = createPlasticoEntity(...array_values($data));
    $this->persistAndFlushWithFind($plastico);

    [$newPreco, $newQuantidade] = array_values($updateData);
    $plastico->preco = $newPreco;
    $plastico->quantidade = $newQuantidade;

    $found = $this->persistAndFlushWithFind($plastico);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($found->id);

    expect($found)->not->toBeNull()
        ->and($found)->toBeInstanceOf(PlasticoEntity::class)
        ->and($found->id)->toBe($plastico->id)
        ->and($found->preco)->toBe($newPreco)
        ->and($found->quantidade)->toBe($newQuantidade)
        ->and($foundResiduo)->toBeInstanceOf(ResiduoEntity::class)
        ->and($foundResiduo->id)->toBe($found->id)
        ->and($foundResiduo->preco)->toBe($newPreco)
        ->and($foundResiduo->quantidade)->toBe($newQuantidade);
})->with('plasticos', 'update data');

it('deve remover PlasticoEntity e ResiduoEntity (entidade/tabela base)', function (array $data) {
    $plastico = createPlasticoEntity(...array_values($data));

    $this->persistAndFlush($plastico);
    $id = $plastico->id;

    $this->removeAndFlush($plastico, true);

    $found = $this->em->getRepository(PlasticoEntity::class)->find($id);
    $foundResiduo = $this->em->getRepository(ResiduoEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundResiduo)->toBeNull();
})->with('plasticos');
