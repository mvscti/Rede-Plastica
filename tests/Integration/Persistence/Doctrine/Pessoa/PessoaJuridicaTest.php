<?php

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaJuridicaEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

$entities = [PessoaJuridicaEntity::class, PessoaEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('pessoas juridicas', [
    'Empresa X' => [
        'data' => [
            'razaoSocial' => 'Empresa X Ltda',
            'nomeFantasia' => 'Empresa X',
            'cnpj' => '12345678000199',
            'endereco' => ['Rua Comercial', '1000', 'Distrito Industrial', '54321678'],
            'coordenadas' => ['latitude' => -23.561684, 'longitude' => -46.625378],
        ]
    ],
    'Empresa Y (Sem Ponto Geográfico)' => [
       'data' => [
           'razaoSocial' => 'Empresa Y S.A.',
           'nomeFantasia' => 'Empresa Y',
           'cnpj' => '98765432000155',
           'endereco' => ['Avenida Empresarial', '2000', 'Centro Empresarial', '87654321'],
           'coordenadas' => null,
       ]
    ],
]);

dataset('update data', [
    'Empresa X' => [
        'updateData' => [
            'razaoSocial' => 'Empresa X Atualizada Ltda',
            'nomeFantasia' => 'Empresa X Atualizada',
            'endereco' => ['Avenida Atualizada', '2000', 'Centro Atualizado', '87654321'],
            'coordenadas' => ['latitude' => -22.561684, 'longitude' => -47.625378],
        ]
    ],
    'Empresa Y (Com Remoção de Ponto Geográfico)' => [
        'updateData' => [
            'razaoSocial' => 'Empresa Y Atualizada S.A.',
            'nomeFantasia' => 'Empresa Y Atualizada',
            'endereco' => ['Rua Nova', '3000', 'Bairro Novo', '13579246'],
            'coordenadas' => null,
        ]
    ],
]);

it('deve persistir e recuperar PessoaJuridicaEntity e os dados comuns em PessoaEntity (entidade/tabela base)', function (
    array $data
) {
    [$razaoSocial, $nomeFantasia, $cnpj, $endereco, $coordenadas] = array_values($data);
    [$rua, $numero, $bairro, $cep] = $endereco;
    $pontoGeografico = $this->createPontoGeografico($coordenadas);

    $pessoa = $this->createPessoaJuridicaEntity($razaoSocial, $nomeFantasia, $cnpj, $endereco, $pontoGeografico);

    $found = $this->persistAndFlushWithFind($pessoa);
    $foundPessoa = $this->em->getRepository(PessoaEntity::class)->find($found->id);

    expect($found)->toBeInstanceOf(PessoaJuridicaEntity::class)
        ->and($found)->toBeInstanceOf(PessoaJuridicaEntity::class)
        ->and($found->id)->toBeGreaterThan(0)
        ->and($found->razaoSocial)->toBe($razaoSocial)
        ->and($found->nomeFantasia)->toBe($nomeFantasia)
        ->and($found->cnpj)->toBe($cnpj)
        ->and($found->endereco->rua)->toBe($rua)
        ->and($found->endereco->numero)->toBe($numero)
        ->and($found->endereco->bairro)->toBe($bairro)
        ->and($found->endereco->cep)->toBe($cep)
        ->and($found->pontoGeografico)->toEqual($pontoGeografico)
        // PessoaEntity (entidade/tabela base)
        ->and($foundPessoa)->toBeInstanceOf(PessoaEntity::class)
        ->and($foundPessoa->id)->toBe($found->id)
        ->and($foundPessoa->endereco->rua)->toBe($rua)
        ->and($foundPessoa->endereco->numero)->toBe($numero)
        ->and($foundPessoa->endereco->bairro)->toBe($bairro)
        ->and($foundPessoa->endereco->cep)->toBe($cep)
        ->and($foundPessoa->pontoGeografico)->toEqual($pontoGeografico);
})->with('pessoas juridicas');

it('deve lançar exceção ao persistir dois objetos PessoaJuridicaEntity com CNPJ igual', function (
    array $pessoaData1, array $pessoaData2
) {
    $pessoa1 = $this->createPessoaJuridicaEntity(...array_values($pessoaData2));
    $pessoa2 = $this->createPessoaJuridicaEntity(...array_values($pessoaData2));

    $this->persistAndFlush($pessoa1);
    $this->expectException(UniqueConstraintViolationException::class);
    $this->persistAndFlush($pessoa2);
})->with([
    'Empresas com CNPJ igual' => [
        [
            'razaoSocial' => 'Empresa X Ltda',
            'nomeFantasia' => 'Empresa X',
            'cnpj' => '12.345.678/0001-99',
            'endereco' => ['Rua X', '10', 'Bairro Y', '12345-678'],
            'coordenadas' => null,
        ],
        [
            'razaoSocial' => 'Empresa Z ME',
            'nomeFantasia' => 'EMPRESA Z',
            'cnpj' => '12.345.678/0001-99', // Mesmo CNPJ
            'endereco' => ['Avenida Z', '20', 'Bairro W', '87654-321'],
            'coordenadas' => null,
        ]
    ],
]);

it('deve atualizar os dados de PessoaJuridicaEntity e os dados comuns em PessoaEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $data['pontoGeografico'] = $this->createPontoGeografico($data['coordenadas']);
    unset($data['coordenadas']);

    $pessoa = $this->createPessoaJuridicaEntity(...array_values($data));
    $this->persistAndFlush($pessoa);

    [$newRazaoSocial, $newNomeFantasia, $newEndereco, $newCoordenadas] = array_values($updateData);
    [$newRua, $newNumero, $newBairro, $newCep] = $newEndereco;
    $newPontoGeografico = $this->createPontoGeografico($newCoordenadas);

    $pessoa->razaoSocial = $newRazaoSocial;
    $pessoa->nomeFantasia = $newNomeFantasia;
    $pessoa->endereco->rua = $newRua;
    $pessoa->endereco->numero = $newNumero;
    $pessoa->endereco->bairro = $newBairro;
    $pessoa->endereco->cep = $newCep;
    $pessoa->pontoGeografico = $newPontoGeografico;
    $this->persistAndFlush($pessoa, true);

    $found = $this->em->getRepository(PessoaJuridicaEntity::class)->find($pessoa->id);
    $foundPessoa = $this->em->getRepository(PessoaEntity::class)->find($found->id);

    expect($found)->toBeInstanceOf(PessoaJuridicaEntity::class)
        ->and($found)->toBeInstanceOf(PessoaJuridicaEntity::class)
        ->and($found->id)->toBe($pessoa->id)
        ->and($found->razaoSocial)->toBe($newRazaoSocial)
        ->and($found->nomeFantasia)->toBe($newNomeFantasia)
        ->and($found->endereco->rua)->toBe($newRua)
        ->and($found->endereco->numero)->toBe($newNumero)
        ->and($found->endereco->bairro)->toBe($newBairro)
        ->and($found->endereco->cep)->toBe($newCep)
        ->and($found->pontoGeografico)->toEqual($newPontoGeografico)
        // PessoaEntity (entidade/tabela base)
        ->and($foundPessoa)->toBeInstanceOf(PessoaEntity::class)
        ->and($foundPessoa->id)->toBe($found->id)
        ->and($foundPessoa->endereco->rua)->toBe($newRua)
        ->and($foundPessoa->endereco->numero)->toBe($newNumero)
        ->and($foundPessoa->endereco->bairro)->toBe($newBairro)
        ->and($foundPessoa->endereco->cep)->toBe($newCep)
        ->and($foundPessoa->pontoGeografico)->toEqual($newPontoGeografico);
})->with('pessoas juridicas', 'update data');

it('deve remover PessoaJuridicaEntity e PessoaEntity (entidade/tabela base)', function (array $data) {
    [$razaoSocial, $nomeFantasia, $cnpj, $endereco, $coordenadas] = array_values($data);

    $pessoa = $this->createPessoaJuridicaEntity(
        $razaoSocial, $nomeFantasia, $cnpj, $endereco, $this->createPontoGeografico($coordenadas)
    );

    $this->persistAndFlush($pessoa);
    $id = $pessoa->id;

    $this->removeAndFlush($pessoa, true);

    $found = $this->em->getRepository(PessoaJuridicaEntity::class)->find($id);
    $foundPessoaBase = $this->em->getRepository(PessoaEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundPessoaBase)->toBeNull();
})->with('pessoas juridicas');