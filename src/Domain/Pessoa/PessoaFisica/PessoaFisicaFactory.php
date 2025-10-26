<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaFisica;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Domain\Pessoa\Pessoa\DTOs\EnderecoDTO;
use App\Domain\Pessoa\Pessoa\ValueObjects\Coordenada;
use App\Domain\Pessoa\Pessoa\ValueObjects\Endereco;
use App\Domain\Pessoa\PessoaFisica\ValueObjects\Cpf;
use App\Domain\Shared\Result;
use DateTimeInterface;

readonly class PessoaFisicaFactory
{
    /**
     * Cria uma instância de PessoaFisica a partir dos dados fornecidos.
     * Retorna um Result contendo a PessoaFisica ou os erros de validação em caso de falha na validação.
     *
     * @param string $nome
     * @param string $cpf
     * @param DateTimeInterface $dataNascimento
     * @param EnderecoDTO $enderecoDTO
     * @param CoordenadaDTO|null $pontoGeograficoDTO
     * @param int|null $id
     * @return Result<PessoaFisica> Result contendo a PessoaFisica ou os erros de validação.
     */
    public function create(
        string $nome,
        string $cpf,
        DateTimeInterface $dataNascimento,
        EnderecoDTO $enderecoDTO,
        ?CoordenadaDTO $pontoGeograficoDTO = null,
        ?int $id = null
    ): Result
    {
        $result = Result::chainAll([
            'cpf' => Cpf::tryValidate($cpf),
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

        return Result::ok(new PessoaFisica(
            nome: $nome,
            cpf: new Cpf($cpf),
            dataNascimento: $dataNascimento,
            endereco: new Endereco(
                $enderecoDTO->getRua(),
                $enderecoDTO->getNumero(),
                $enderecoDTO->getBairro(),
                $enderecoDTO->getCep()
            ),
            pontoGeografico: $hasPontoGeografico ?
                new Coordenada($pontoGeograficoDTO->getLatitude(), $pontoGeograficoDTO->getLongitude())
                : null,
            id: $id
        ));
    }
}