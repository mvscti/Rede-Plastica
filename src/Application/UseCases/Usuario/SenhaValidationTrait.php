<?php

declare(strict_types=1);

namespace App\Application\UseCases\Usuario;

use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;

trait SenhaValidationTrait
{
    private function validateSenhaPlainText(string $plainText): Result
    {
        $result = new ValidationResult();

        if (empty($plainText)) {
            $result->addError('A senha não pode ser vazia.');
        }

        if (strlen($plainText) < 6) {
            $result->addError('A senha deve ter pelo menos 6 caracteres.');
        }

        if (strlen($plainText) > 50) {
            $result->addError('A senha não pode ter mais de 50 caracteres.');
        }

        if (preg_match('/\s/', $plainText)) {
            $result->addError('A senha não pode conter espaços em branco.');
        }

        if (!preg_match('/[A-Z]/', $plainText)) {
            $result->addError('A senha deve conter pelo menos uma letra maiúscula.');
        }

        if (!preg_match('/[a-z]/', $plainText)) {
            $result->addError('A senha deve conter pelo menos uma letra minúscula.');
        }

        if (!preg_match('/[0-9]/', $plainText)) {
            $result->addError('A senha deve conter pelo menos um número.');
        }

        if (!preg_match('/[!@#$%&*]/', $plainText)) {
            $result->addError('A senha deve conter pelo menos um caractere especial (!@#$%&*).');
        }

        if (preg_match('/[^A-Za-z0-9!@#$%&*]/', $plainText)) {
            $result->addError('A senha deve conter apenas letras, números e os caracteres especiais !@#$%&*.');
        }

        return $result->isValid() ? Result::ok(true) : Result::fail($result->getErrors());
    }
}