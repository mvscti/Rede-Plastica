<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Controllers\Helpers\Results\PaginationParamsDTO;
use App\Infrastructure\Http\Controllers\Helpers\Results\SortingParams;
use App\Infrastructure\Http\Controllers\Helpers\ResponseBuilder;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract readonly class AbstractController
{
    public function __construct(
        protected ResponseBuilder $responseBuilder,
        protected ValidationErrorBuilder $errorBuilder,
    ) {
        $this->responseBuilder->changeMaxItemsPerPage($this->getMaxItemsPerPage());
    }

    /**
     * Extrai os parâmetros de paginação da requisição.
     *
     * @param Request $request Instância da requisição PSR-7.
     * @return PaginationParamsDTO Objeto DTO contendo os parâmetros de paginação.
     */
    protected function extractPaginationParams(Request $request): PaginationParamsDTO
    {
        $queryParams = $request->getQueryParams();
        $page = (int)($queryParams['page'] ?? 1);
        $perPage = (int)($queryParams['per_page'] ?? $this->getMaxItemsPerPage());

        $perPage = max(1, min($perPage, $this->getMaxItemsPerPage()));
        $page = max(1, $page);

        return new PaginationParamsDTO($page, $perPage);
    }

    /**
     * Extrai os parâmetros de ordenação da requisição.
     *
     * @param Request $request Instância da requisição PSR-7.
     * @return SortingParams Objeto DTO contendo os parâmetros de ordenação.
     */
    protected function extractSortingParams(Request $request): SortingParams
    {
        $queryParams = $request->getQueryParams();
        $sort = $queryParams['sort'] ?? '';
        $sortingParams = new SortingParams();

        foreach (explode(',', $sort) as $field) {
            $field = trim($field);

            if ($field === '') {
                continue;
            }

            $sortingParams->addField($field);
        }

        return $sortingParams;
    }

    /**
     * Extrai os filtros da requisição com base nos campos permitidos.
     *
     * @param Request $request
     * @param array $allowedFields
     * @return array
     */
    protected function extractFilters(Request $request, array $allowedFields): array
    {
        $queryParams = $request->getQueryParams();
        $filters = [];

        foreach ($allowedFields as $field) {
            if (isset($queryParams[$field])) {
                $filters[$field] = $queryParams[$field];
            }
        }

        return $filters;
    }

    protected function getMaxItemsPerPage(): int
    {
        return 10;
    }
}