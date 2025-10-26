<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa\DTOs;

readonly class EnderecoDTO
{
    public function __construct(
        private ?string $rua = null,
        private ?string $numero = null,
        private ?string $bairro = null,
        private ?string $cep = null,
    )
    {
    }

    public function getRua(): ?string
    {
        return $this->rua;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function getBairro(): ?string
    {
        return $this->bairro;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }
}