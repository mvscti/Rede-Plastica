<?php

declare(strict_types=1);

namespace App\Domain\Shared;

/**
 * @template T
 */
readonly class Result
{
    /**
     * Construtor da classe Result.
     *
     * @param bool $success Indica se a operação foi bem-sucedida.
     * @param T|null $value Valor retornado (pode ser de qualquer tipo).
     * @param array $errors Lista de erros, caso existam.
     */
    private function __construct(
        private bool $success,
        private mixed $value = null,
        private array $errors = []
    ){
    }

    public static function ok($value): self
    {
        return new self(true, $value);
    }

    public static function fail(array|string $errors): self
    {
        return new self(false, null, is_array($errors) ? $errors : [$errors]);
    }

    /**
     * Executa uma cadeia de métodos Result, acumulando todos os erros.
     * Retorna sucesso apenas se todos os passos forem bem-sucedidos.
     *
     * @param array $steps
     * @return self
     */
    public static function chainAll(array $steps): self
    {
        $value = null;
        $allErrors = [];

        foreach ($steps as $key => $step) {
            if ($step->isFailure()) {
                foreach ($step->getErrors() as $error) {
                    $allErrors[$key][] = $error;
                }
            }

            $value = $step->getValue();
        }

        if (!empty($allErrors)) {
            return self::fail($allErrors);
        }

        return self::ok($value);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * Retorna o valor armazenado no Result.
     *
     * @return T|null O valor armazenado, ou null se a operação falhou.
     */
    public function getValue() {
        return $this->value;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}