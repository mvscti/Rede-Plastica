<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Queries;

class QueryParams
{
    private array $filters = [];
    private array $orderBy = [];
    private int $page = 1;
    private int $perPage = 10;

    public function addFilter(string $field, mixed $value): self
    {
        $this->filters[$field] = $value;
        return $this;
    }

    /**
     * Adiciona múltiplos filtros.
     * @param array<string, mixed> $filters - Array associativo onde a chave é o campo e o valor é o valor do filtro.
     * @return $this
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $field => $value) {
            $this->addFilter($field, $value);
        }

        return $this;
    }

    public function addOrderByField(string $field, string $direction = 'ASC'): self
    {
        $this->orderBy[$field] = strtoupper($direction);
        return $this;
    }

    /**
     * Adiciona múltiplos campos de ordenação.
     * @param array<string, string> $fields - Array associativo onde a chave é o campo e o valor é 'ASC' ou 'DESC'.
     * @return $this
     */
    public function addOrderByFields(array $fields): self
    {
        foreach ($fields as $field => $direction) {
            $this->addOrderByField($field, $direction);
        }

        return $this;
    }

    public function setPage(int $page): self
    {
        $this->page = max($page, 1);
        return $this;
    }

    public function setPerPage(int $perPage): self
    {
        $this->perPage = max($perPage, 1);
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}