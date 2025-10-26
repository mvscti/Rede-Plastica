<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence\Doctrine\Pessoa;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Infrastructure\Persistence\Entities\Pessoa\Embeddables\Endereco;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaFisicaEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaJuridicaEntity;
use DateTimeImmutable;

trait PessoaTestHelpersTrait
{
    public function createPontoGeografico(?array $coordenadas): ?CoordenadaDTO
    {
        return $coordenadas
            ? new CoordenadaDTO($coordenadas['latitude'], $coordenadas['longitude'])
            : null;
    }

    public function createPessoaFisicaEntity(
        string $nome, string $cpf, string $dataNascimento, array $endereco, ?CoordenadaDTO $pontoGeografico = null
    ): PessoaFisicaEntity
    {
        $pessoa = new PessoaFisicaEntity();
        $pessoa->nome = $nome;
        $pessoa->cpf = $cpf;
        $pessoa->dataNascimento = new DateTimeImmutable($dataNascimento);
        $pessoa->endereco = $this->createEnderecoEmbeddable(...$endereco);
        $pessoa->pontoGeografico = $pontoGeografico;
        return $pessoa;
    }

    public function createPessoaJuridicaEntity(
        string $razaoSocial, string $nomeFantasia, string $cnpj, array $endereco, ?CoordenadaDTO $pontoGeografico = null
    ): PessoaJuridicaEntity
    {
        $pessoa = new PessoaJuridicaEntity();
        $pessoa->razaoSocial = $razaoSocial;
        $pessoa->nomeFantasia = $nomeFantasia;
        $pessoa->cnpj = $cnpj;
        $pessoa->endereco = $this->createEnderecoEmbeddable(...$endereco);
        $pessoa->pontoGeografico = $pontoGeografico;
        return $pessoa;
    }

    private function createEnderecoEmbeddable(string $rua, string $numero, string $bairro, string $cep): Endereco
    {
        $endereco = new Endereco();
        $endereco->rua = $rua;
        $endereco->numero = $numero;
        $endereco->bairro = $bairro;
        $endereco->cep = $cep;

        return $endereco;
    }
}