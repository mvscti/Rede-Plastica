<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Helpers;

trait SymfonyValidationErrorHandlerTrait
{
    protected function handleValidationErrors($violations, $errorBuilder): void
    {
        foreach ($violations as $violation) {
            $field = trim($violation->getPropertyPath(), '[]');
            $errorBuilder->withFieldError($field, $violation->getMessage());
        }
    }
}