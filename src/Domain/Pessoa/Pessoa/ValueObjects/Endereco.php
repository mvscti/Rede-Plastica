<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa\ValueObjects;

use App\Domain\Pessoa\Pessoa\Exceptions\InvalidEnderecoException;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use Stringable;

final readonly class Endereco implements Stringable
{
    private ?string $rua;
    private ?string $numero;
    private ?string $bairro;
    private ?Cep $cep;

    public function __construct(
        ?string $rua = null,
        ?string $numero = null,
        ?string $bairro = null,
        ?string $cep = null,
    )
    {
        $rua = trim($rua);
        $numero = trim($numero);
        $bairro = trim($bairro);
        $this->validate($rua, $numero, $bairro, $cep);

        $this->rua = $rua;
        $this->numero = $numero;
        $this->bairro = $bairro;
        $this->cep = new Cep($cep);
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
        return (string)$this->cep;
    }

    public function getFormattedCep(): ?string
    {
        return $this->cep?->getFormattedValue();
    }

    public function equals(Endereco $other): bool
    {
        return $this->rua === $other->getRua()
            && $this->numero === $other->getNumero()
            && $this->bairro === $other->getBairro()
            && $this->cep == $other->getCep();
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }

    /**
     * Tenta validar o endereço e retorna um Result.
     *
     * @param string|null $rua
     * @param string|null $numero
     * @param string|null $bairro
     * @param string|null $cep
     * @return Result<bool>
     */
    public static function tryValidate(
        ?string $rua = null,
        ?string $numero = null,
        ?string $bairro = null,
        ?string $cep = null
    ): Result
    {
        $rua = trim($rua);
        $numero = trim($numero);
        $bairro = trim($bairro);
        $result = new ValidationResult();

        if (empty($rua)) {
            $result->addError("A rua não pode ser vazio.");
        }

        if (strlen($rua) > 150) {
            $result->addError("A rua deve conter no máximo 150 caracteres.");
        }

        if (empty($numero)) {
            $result->addError("O número não pode ser vazio.");
        }

        if (strlen($numero) > 20) {
            $result->addError("O número deve conter no máximo 20 caracteres.");
        }

        if (empty($bairro)) {
            $result->addError("O bairro não pode ser vazio.");
        }

        if (strlen($bairro) > 100) {
            $result->addError("O bairro deve conter no máximo 100 caracteres.");
        }

        $cepResult = Cep::tryValidate($cep);

        if ($cepResult->isFailure()) {
            $result->addErrors($cepResult->getErrors());
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    /**
     * @throws InvalidEnderecoException
     */
    private function validate(
        ?string $rua = null,
        ?string $numero = null,
        ?string $bairro = null,
        ?string $cep = null
    ): void
    {
        $result = $this::tryValidate($rua, $numero, $bairro, $cep);

        if ($result->isFailure()) {
            throw new InvalidEnderecoException($result->getErrors()[0] ?? "Erro de validação no endereço.");
        }
    }
}