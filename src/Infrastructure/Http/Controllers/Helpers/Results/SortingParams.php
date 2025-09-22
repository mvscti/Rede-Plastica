<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Helpers\Results;

class SortingParams
{
    private array $orderBy = [];

    /**
     * Adiciona um campo de ordenação.
     * @param string $field - Nome do campo, prefixado por '-' para DESC ou '+' para ASC (padrão).
     * @return $this
     */
    public function addField(string $field): self
    {
        $value = str_starts_with($field, '-') ? 'DESC' : 'ASC';
        $field = ltrim($field, '+-');
        $this->orderBy[$field] = $value;
        return $this;
    }

    /**
     * Retorna os parâmetros de ordenação como um array associativo.
     * @return array<string, string> Array associativo contendo ['campo' => 'ASC' | 'DESC'].
     */
    public function toAssociativeArray(): array
    {
        return $this->orderBy;
    }
}