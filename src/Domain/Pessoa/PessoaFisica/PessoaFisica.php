<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaFisica;

use App\Domain\Pessoa\Pessoa\Pessoa;
use App\Domain\Pessoa\Pessoa\ValueObjects\Coordenada;
use App\Domain\Pessoa\Pessoa\ValueObjects\Endereco;
use App\Domain\Pessoa\PessoaFisica\ValueObjects\Cpf;
use DateTimeInterface;

class PessoaFisica extends Pessoa
{
    public function __construct(
        private string $nome,
        private Cpf $cpf,
        private DateTimeInterface $dataNascimento,
        Endereco $endereco,
        ?Coordenada $pontoGeografico = null,
        ?int $id = null
    )
    {
        parent::__construct($endereco, $pontoGeografico, $id);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCpf(): string
    {
        return (string)$this->cpf;
    }

    public function getFormattedCpf(): string
    {
        return $this->cpf->getFormattedValue();
    }

    public function getDataNascimento(): DateTimeInterface
    {
        return $this->dataNascimento;
    }
}