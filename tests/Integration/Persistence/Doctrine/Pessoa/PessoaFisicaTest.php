<?php

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaFisicaEntity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

$entities = [PessoaFisicaEntity::class, PessoaEntity::class];

beforeEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

afterEach(function () use ($entities) {
    $this->cleanTablesFromEntities($entities);
});

dataset('pessoas fisicas', [
    'João' => [
        'data' => [
            'nome' => 'João',
            'cpf' => '12345678900',
            'dataNascimento' => '1990-01-01',
            'endereco' => ['Rua A', '123', 'Centro', '12345678'],
            'coordenadas' => ['latitude' => -23.55052, 'longitude' => -46.633308]
        ]
    ],
    'Maria (Sem Ponto Geográfico)' => [
        'data'=> [
            'nome' => 'Maria',
            'cpf' => '98765432100',
            'dataNascimento' => '1985-05-15',
            'endereco' => ['Avenida B', '456', 'Bairro Alto', '87654321'],
            'coordenadas' => null
        ]
    ],
]);

dataset('update data', [
    'João' => [
        'updateData' => [
            'nome' => 'João Silva',
            'endereco' => ['Rua Atualizada', '2', 'Bairro Atualizado', '22222222'],
            'coordenadas' => ['latitude' => -22.00000, 'longitude' => -43.00000 ],
        ]
    ],
    'Maria (Com Remoção de Ponto Geográfico)' => [
        'updateData' => [
            'nome' => 'Maria Oliveira',
            'endereco' => ['Avenida Nova', '789', 'Bairro Novo', '98765432'],
            'coordenadas' => null,
        ]
    ],
]);

it('deve persistir e recuperar PessoaFisicaEntity e os dados comuns em PessoaEntity (entidade/tabela base)', function (
    array $data
) {
    [$nome, $cpf, $dataNascimento, $endereco, $coordenadas] = array_values($data);
    [$rua, $numero, $bairro, $cep] = $endereco;
    $pontoGeografico = $this->createPontoGeografico($coordenadas);

    $pessoa = $this->createPessoaFisicaEntity($nome, $cpf, $dataNascimento, $endereco, $pontoGeografico);

    $found = $this->persistAndFlushWithFind($pessoa);
    $foundPessoa = $this->em->getRepository(PessoaEntity::class)->find($found->id);

    expect($found)->toBeInstanceOf(PessoaFisicaEntity::class)
        ->and($found)->toBeInstanceOf(PessoaEntity::class)
        ->and($found->id)->toBeGreaterThan(0)
        ->and($found->nome)->toBe($nome)
        ->and($found->cpf)->toBe($cpf)
        ->and($found->dataNascimento->format('Y-m-d'))->toBe($dataNascimento)
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
})->with('pessoas fisicas');

it('deve lançar exceção ao persistir dois objetos PessoaFisicaEntity com CPF igual', function (
    array $pessoaData1, array $pessoaData2
) {
    $pessoa1 = $this->createPessoaFisicaEntity(...array_values($pessoaData1));
    $pessoa2 = $this->createPessoaFisicaEntity(...array_values($pessoaData2));

    $this->persistAndFlush($pessoa1);
    $this->expectException(UniqueConstraintViolationException::class);
    $this->persistAndFlush($pessoa2);
})->with([
    'Pessoas com CPF igual' => [
        [
            'nome' => 'Ana',
            'cpf' => '123.456.789-00',
            'dataNascimento' => '1992-03-10',
            'endereco' => ['Rua X', '10', 'Bairro Y', '12345-678'],
            'coordenadas' => null,
        ],
        [
            'nome' => 'Bruno',
            'cpf' => '123.456.789-00', // Mesmo CPF
            'dataNascimento' => '1988-07-20',
            'endereco' => ['Avenida Z', '20', 'Bairro W', '87654-321'],
            'coordenadas' => null,
        ],
    ]
]);

it('deve atualizar os dados de PessoaFisicaEntity e os dados comuns em PessoaEntity (entidade/tabela base)', function (
    array $data, array $updateData
) {
    $data['pontoGeografico'] = $this->createPontoGeografico($data['coordenadas']);
    unset($data['coordenadas']);

    $pessoa = $this->createPessoaFisicaEntity(...array_values($data));
    $this->persistAndFlush($pessoa);

    [$newNome, $newEndereco, $newCoordenadas] = array_values($updateData);
    [$newRua, $newNumero, $newBairro, $newCep] = $newEndereco;
    $newPontoGeografico = $this->createPontoGeografico($newCoordenadas);

    $pessoa->nome = $newNome;
    $pessoa->endereco->rua = $newRua;
    $pessoa->endereco->numero = $newNumero;
    $pessoa->endereco->bairro = $newBairro;
    $pessoa->endereco->cep = $newCep;
    $pessoa->pontoGeografico = $newPontoGeografico;
    $this->persistAndFlush($pessoa, true);

    $found = $this->em->getRepository(PessoaFisicaEntity::class)->find($pessoa->id);
    $foundPessoa = $this->em->getRepository(PessoaEntity::class)->find($found->id);

    expect($found)->toBeInstanceOf(PessoaFisicaEntity::class)
        ->and($found)->toBeInstanceOf(PessoaEntity::class)
        ->and($found->id)->toBe($pessoa->id)
        ->and($found->nome)->toBe($newNome)
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
})->with('pessoas fisicas', 'update data');

it('deve remover PessoaFisicaEntity e PessoaEntity (entidade/tabela base)', function (array $data) {
    [$nome, $cpf, $dataNascimento, $endereco, $coordenadas] = array_values($data);
    $pessoa = $this->createPessoaFisicaEntity(
        $nome, $cpf, $dataNascimento, $endereco, $this->createPontoGeografico($coordenadas)
    );

    $this->persistAndFlush($pessoa);
    $id = $pessoa->id;

    $this->removeAndFlush($pessoa, true);

    $found = $this->em->getRepository(PessoaFisicaEntity::class)->find($id);
    $foundPessoa = $this->em->getRepository(PessoaEntity::class)->find($id);

    expect($found)->toBeNull()
        ->and($foundPessoa)->toBeNull();
})->with('pessoas fisicas');