<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Helpers;

final class ValidationErrorBuilder
{
    private array $errors = [];
    private int $errorsCount = 0 {
        get {
            return $this->errorsCount;
        }
    }

    public function withError(string $message): self
    {
        return $this->addError($message);
    }

    public function withFieldError(string $field, string $message): self
    {
        return $this->addError($message, $field);
    }

    public function hasErrors(): bool
    {
        return $this->errorsCount > 0;
    }

    public function build(): array
    {
        return $this->errors;
    }

    private function addError(string $message, ?string $field = null): self
    {
        $error = [
            ...($field !== null ? ['field' => $field] : []),
            'message' => $message,
        ];

        $this->errors[] = $error;
        $this->errorsCount++;

        return $this;
    }
}