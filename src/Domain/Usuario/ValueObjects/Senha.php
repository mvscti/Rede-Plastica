<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

use App\Domain\Usuario\Exceptions\InvalidSenhaException;
use Stringable;

final readonly class Senha implements Stringable
{
    private string $hash;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->hash = $value;
    }

    public function value(): string
    {
        return $this->hash;
    }

    public function equals(Senha $other): bool
    {
        return $this->hash === $other->value();
    }

    public function __toString(): string
    {
        return $this->hash;
    }

    private function validate(string $textToValidate): void
    {
        if (empty($textToValidate)) {
            throw new InvalidSenhaException("A senha (hash) não pode ser vazia.");
        }

        if (preg_match('/\s/', $textToValidate)) {
            throw new InvalidSenhaException("A senha (hash) não pode conter espaços em branco.");
        }
    }
}