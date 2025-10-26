<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaFisica\ValueObjects;

use App\Domain\Pessoa\PessoaFisica\Exceptions\InvalidCpfException;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use Stringable;

final readonly class Cpf implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $value = preg_replace('/\D/', '', $value);
        $this->validate($value);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormattedValue(): string
    {
        return substr($this->value, 0, 3) . '.' .
               substr($this->value, 3, 3) . '.' .
               substr($this->value, 6, 3) . '-' .
               substr($this->value, 9, 2);
    }

    public function equals(Cpf $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Tenta validar o CPF e retorna um Result.
     *
     * @param string $value
     * @return Result<bool>
     */
    public static function tryValidate(string $value): Result
    {
        $value = preg_replace('/\D/', '', $value);
        $result = new ValidationResult();

        if (empty($value)) {
            $result->addError("O CPF não pode ser vazio.");
        }

        if (strlen($value) !== 11) {
            $result->addError("O CPF deve conter 11 caracteres.");
        }

        if (!self::isValidCpf($value)) {
            $result->addError("O CPF é inválido.");
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    private function validate(string $value): void
    {
        $result = $this::tryValidate($value);

        if ($result->isFailure()) {
            throw new InvalidCpfException(implode(", ", $result->getErrors()));
        }
    }

    /**
     * Valida o CPF.
     *
     * @param string $cpf
     * @return bool
     */
    private static function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) != 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}