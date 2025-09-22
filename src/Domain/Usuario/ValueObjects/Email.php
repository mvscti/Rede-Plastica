<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use App\Domain\Usuario\Exceptions\InvalidEmailException;
use Stringable;

final readonly class Email implements Stringable
{
    private string $value;

    /**
     * @throws InvalidEmailException
     */
    public function __construct(string $value)
    {
        $value = trim($value);
        $this->validate($value);
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Tenta validar o email e retorna um Result.
     *
     * @param string $email
     * @return Result<bool>
     */
    public static function tryValidate(string $email): Result
    {
        $email = trim($email);
        $result = new ValidationResult();

        if (empty($email)) {
            $result->addError("O email não pode ser vazio.");
        }

        if (strlen($email) > 50) {
            $result->addError("O email deve conter no máximo 50 caracteres.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result->addError("O email informado é inválido.");
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    /**
     * @throws InvalidEmailException
     */
    private function validate(string $email): void
    {
        $result = $this::tryValidate($email);

        if ($result->isFailure()) {
            throw new InvalidEmailException($result->getErrors()[0] ?? "Erro de validação no email.");
        }
    }
}