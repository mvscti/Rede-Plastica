<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaJuridica\ValueObjects;

use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use Stringable;

final readonly class Cnpj implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $value = preg_replace('/\D/', '', $value);

    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormattedValue(): string
    {
        return substr($this->value, 0, 2) . '.' .
               substr($this->value, 2, 3) . '.' .
               substr($this->value, 5, 3) . '/' .
               substr($this->value, 8, 4) . '-' .
               substr($this->value, 12, 2);
    }

    public function equals(Cnpj $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Tenta validar o CNPJ e retorna um Result.
     *
     * @param string $value
     * @return Result<bool>
     */
    public static function tryValidate(string $value): Result
    {
        $value = preg_replace('/\D/', '', $value);
        $result = new ValidationResult();

        if (empty($value)) {
            $result->addError("O CNPJ não pode ser vazio.");
        }

        if (strlen($value) !== 14) {
            $result->addError("O CNPJ deve conter 14 caracteres.");
        }

        if (!self::isValidCnpj($value)) {
            $result->addError("O CNPJ é inválido.");
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) != 14) {
            return false;
        }


        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }


        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}