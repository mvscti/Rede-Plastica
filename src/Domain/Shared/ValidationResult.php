<?php

declare(strict_types=1);

namespace App\Domain\Shared;

class ValidationResult
{
    private array $errors = [];

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function addErrors(array $messages): void
    {
        foreach ($messages as $message) {
            $this->addError($message);
        }
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}