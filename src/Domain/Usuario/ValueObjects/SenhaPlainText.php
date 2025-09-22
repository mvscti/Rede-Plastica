<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use App\Domain\Usuario\Exceptions\InvalidSenhaPlainTextException;
use Stringable;

final readonly class SenhaPlainText implements Stringable
{
    private string $value;

    public function __construct(string $plainText)
    {
        $this->value = $plainText;
    }

    public function value(): string
    {
        return $this->hash;
    }

    public static function isHashed(string $toVerify): bool
    {
        return password_get_info($toVerify)['algo'] !== 0;
    }

    public function verify(string $plainText): bool
    {
        return password_verify($plainText, $this->hash);
    }

    public function needsRehash(): bool
    {
        return password_needs_rehash($this->hash, PASSWORD_DEFAULT);
    }

    public function equals(SenhaPlainText $other): bool
    {
        return $this->hash === $other->value();
    }

    public function __toString(): string
    {
        return $this->hash;
    }

    /**
     * Tenta validar o email e retorna um Result.
     *
     * @param string $plainText
     * @return Result<bool>
     */
    public static function tryValidate(string $plainText): Result
    {
        $plainText = trim($plainText);
        $result = new ValidationResult();

        if (empty($plainText)) {
            $result->addError("O senha não pode ser vazio.");
        }

        if (strlen($plainText) > 50) {
            $result->addError("O email deve conter no máximo 50 caracteres.");
        }

        if (!filter_var($plainText, FILTER_VALIDATE_EMAIL)) {
            $result->addError("O email informado é inválido.");
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    /**
     * @throws InvalidSenhaPlainTextException
     */
    private function validate(string $plainText): void
    {
        $result = $this::tryValidate($plainText);

        if ($result->isFailure()) {
            throw new InvalidSenhaPlainTextException(
                $result->getErrors()[0] ?? "Erro de validação na senha (plain text)."
            );
        }
    }
}