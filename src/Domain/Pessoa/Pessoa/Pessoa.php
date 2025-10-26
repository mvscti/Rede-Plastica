<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Domain\Pessoa\Pessoa\DTOs\EnderecoDTO;
use App\Domain\Pessoa\Pessoa\ValueObjects\Coordenada;
use App\Domain\Pessoa\Pessoa\ValueObjects\Endereco;
use App\Domain\Shared\AbstractDomainEntity;
use App\Domain\Shared\Result;

abstract class Pessoa extends AbstractDomainEntity
{
    public function __construct(
        protected Endereco $endereco,
        protected ?Coordenada $pontoGeografico = null,
        ?int $id = null,
    )
    {
        parent::__construct($id);
    }

    public function getRua(): ?string
    {
        return $this->endereco->getRua();
    }

    public function getNumero(): ?string
    {
        return $this->endereco->getNumero();
    }

    public function getBairro(): ?string
    {
        return $this->endereco->getBairro();
    }

    public function getCep(): ?string
    {
        return $this->endereco->getCep();
    }

    public function getFormattedCep(): ?string
    {
        return $this->endereco->getFormattedCep();
    }

    public function getLatitude(): ?float
    {
        return $this->pontoGeografico?->getLatitude();
    }

    public function getLongitude(): ?float
    {
        return $this->pontoGeografico?->getLongitude();
    }

    /**
     * Atualiza o endereço da pessoa com os dados fornecidos no EnderecoDTO.
     * Retorna um Result indicando sucesso ou falha na validação dos dados.
     *
     * @param EnderecoDTO $enderecoDTO
     * @return Result<Pessoa> Result indicando sucesso ou falha na validação dos dados.
     */
    public function changeEndereco(EnderecoDTO $enderecoDTO): Result
    {
        $result = Endereco::tryValidate(
            $enderecoDTO->getRua(),
            $enderecoDTO->getNumero(),
            $enderecoDTO->getBairro(),
            $enderecoDTO->getCep()
        );

        if ($result->isFailure()) {
            return Result::fail($result->getErrors());
        }

        $this->endereco = new Endereco(
            $enderecoDTO->getRua(),
            $enderecoDTO->getNumero(),
            $enderecoDTO->getBairro(),
            $enderecoDTO->getCep()
        );

        return Result::ok($this);
    }

    /**
     * Atualiza o ponto geográfico da pessoa com os dados fornecidos.
     * Retorna um Result indicando sucesso ou falha na validação dos dados.
     *
     * @param CoordenadaDTO $coordenadaDTO
     *
     * @return Result<Pessoa> Result indicando sucesso ou falha na validação dos dados.
     */
    public function changePontoGeografico(CoordenadaDTO $coordenadaDTO): Result
    {
        [ $latitude, $longitude ] = [ $coordenadaDTO->getLatitude(), $coordenadaDTO->getLongitude()];

        if ($latitude === null && $longitude === null) {
            $this->pontoGeografico = null;
            return Result::ok($this);
        }

        $result = Coordenada::tryValidate($latitude, $longitude);

        if ($result->isFailure()) {
            return Result::fail($result->getErrors());
        }

        $this->pontoGeografico = new Coordenada($latitude, $longitude);
        return Result::ok($this);
    }
}