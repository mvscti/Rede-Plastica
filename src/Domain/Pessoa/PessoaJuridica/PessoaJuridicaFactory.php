<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaJuridica;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Domain\Pessoa\Pessoa\DTOs\EnderecoDTO;
use App\Domain\Pessoa\Pessoa\ValueObjects\Coordenada;
use App\Domain\Pessoa\Pessoa\ValueObjects\Endereco;
use App\Domain\Pessoa\PessoaJuridica\ValueObjects\Cnpj;
use App\Domain\Shared\Result;

readonly class PessoaJuridicaFactory
{
    /**
     * Cria uma instância de PessoaJuridica a partir dos dados fornecidos.
     * Retorna um Result contendo a PessoaJuridica ou os erros de validação em caso de falha na validação.
     *
     * @param string $nomeFantasia
     * @param string $razaoSocial
     * @param string $cnpj
     * @param EnderecoDTO $enderecoDTO
     * @param CoordenadaDTO|null $pontoGeograficoDTO
     * @param int|null $id
     * @return Result<PessoaJuridica> Result contendo a PessoaJuridica ou os erros de validação.
     */
    public function create(
        string $nomeFantasia,
        string $razaoSocial,
        string $cnpj,
        EnderecoDTO $enderecoDTO,
        ?CoordenadaDTO $pontoGeograficoDTO = null,
        ?int $id = null
    ): Result
    {
        $result = Result::chainAll([
            'cnpj' => Cnpj::tryValidate($cnpj),
            'endereco' => Endereco::tryValidate(
                $enderecoDTO->getRua(),
                $enderecoDTO->getNumero(),
                $enderecoDTO->getBairro(),
                $enderecoDTO->getCep()
            ),
            'ponto_geografico' => $pontoGeograficoDTO ? Coordenada::tryValidate(
                $pontoGeograficoDTO->getLatitude(),
                $pontoGeograficoDTO->getLongitude()
            ) : Result::ok(null)
        ]);

        if ($result->isFailure()) {
            return Result::fail($result->getErrors());
        }

        $hasPontoGeografico = $pontoGeograficoDTO?->getLatitude() !== null
            && $pontoGeograficoDTO?->getLongitude() !== null;

        return Result::ok(new PessoaJuridica(
            $nomeFantasia,
            $razaoSocial,
            new Cnpj($cnpj),
            new Endereco(
                $enderecoDTO->getRua(),
                $enderecoDTO->getNumero(),
                $enderecoDTO->getBairro(),
                $enderecoDTO->getCep()
            ),
            $hasPontoGeografico ?
                new Coordenada($pontoGeograficoDTO->getLatitude(), $pontoGeograficoDTO->getLongitude())
                : null,
            $id
        ));
    }
}