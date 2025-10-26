<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaJuridica;

use App\Domain\Pessoa\Pessoa\Pessoa;
use App\Domain\Pessoa\Pessoa\ValueObjects\Coordenada;
use App\Domain\Pessoa\Pessoa\ValueObjects\Endereco;
use App\Domain\Pessoa\PessoaJuridica\ValueObjects\Cnpj;

class PessoaJuridica extends Pessoa
{
    public function __construct(
        private string $nomeFantasia,
        private string $razaoSocial,
        private Cnpj $cnpj,
        Endereco $endereco,
        ?Coordenada $pontoGeografico = null,
        ?int $id = null
    )
    {
        parent::__construct($endereco, $pontoGeografico, $id);
    }

    public function getNomeFantasia(): string
    {
        return $this->nomeFantasia;
    }

    public function getRazaoSocial(): string
    {
        return $this->razaoSocial;
    }

    public function getCnpj(): string
    {
        return (string)$this->cnpj;
    }

    public function getFormattedCnpj(): string
    {
        return $this->cnpj->getFormattedValue();
    }
}