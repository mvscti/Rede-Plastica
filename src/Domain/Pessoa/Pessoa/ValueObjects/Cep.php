<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa\ValueObjects;

use App\Domain\Pessoa\Pessoa\Exceptions\InvalidCepException;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use Stringable;

final readonly class Cep implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $value = str_replace('-', '', $value);
        $this->validate($value);

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getFormattedValue(): string
    {
        return substr($this->value, 0, 5) . '-' . substr($this->value, 5, 3);
    }

    public function equals(Cep $other): bool
    {
        return $this->value === $other->getValue();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Tenta validar o CEP e retorna um Result.
     *
     * @param string $cep
     * @return Result<bool>
     */
    public static function tryValidate(string $cep): Result
    {
        $result = new ValidationResult();

        if (empty($cep)) {
            $result->addError("O cep não pode ser vazio.");
        }

        $cep = str_replace('-', '', $cep);

        if (strlen($cep) !== 8) {
            $result->addError("O cep deve conter exatamente 8 caracteres (sem contar o hífen).");
        }

        if (!preg_match('/^\d{8}$/', $cep)) {
            $result->addError("O cep informado é inválido.");
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    /**
     * @throws InvalidCepException
     */
    private function validate(string $cep): void
    {
        $result = $this::tryValidate($cep);

        if ($result->isFailure()) {
            throw new InvalidCepException($result->getErrors()[0] ?? "Erro de validação no CEP.");
        }
    }
}