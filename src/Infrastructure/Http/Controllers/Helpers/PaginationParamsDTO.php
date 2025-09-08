<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Helpers;

final readonly class PaginationParamsDTO
{
    public function __construct(
        private int $page,
        private int $perPage,
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Retorna os parâmetros de paginação como um array indexado.
     * @return array<int> Array indexado contendo [page, perPage].
     */
    public function toIndexedArray(): array
    {
        return [$this->page, $this->perPage];
    }

    /**
     * Retorna os parâmetros de paginação como um array associativo.
     * @return array<string, int> Array associativo contendo ['page' => page, 'per_page' => perPage].
     */
    public function toAssociativeArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
        ];
    }
}