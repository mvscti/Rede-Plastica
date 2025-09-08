<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Infrastructure\Http\Controllers\Helpers\HttpStatusCode;
use App\Infrastructure\Http\Controllers\Helpers\PaginationParamsDTO;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract readonly class AbstractController
{
    private const int MAX_ITEMS_PER_PAGE = 10;

    public function __construct(protected ValidationErrorBuilder $errorBuilder)
    {
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
        $perPage = (int)($queryParams['per_page'] ?? self::MAX_ITEMS_PER_PAGE);

        $perPage = max(1, min($perPage, self::MAX_ITEMS_PER_PAGE));
        $page = max(1, $page);

        return new PaginationParamsDTO($page, $perPage);
    }

    protected function successResponse(
        Response $response,
        string $message,
        array $data = [],
        HttpStatusCode|int $statusCode = HttpStatusCode::OK
    ): Response
    {
        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => $message,
            'data' => $this->ensureDataResponseIsArray($data),
        ], $statusCode);
    }

    protected function paginatedResponse(
        Response $response,
        string $message,
        array $data,
        int $totalItems,
        int $page = 1,
        int $perPage = 10,
        HttpStatusCode|int $statusCode = HttpStatusCode::OK
    ): Response
    {
        $perPage = max(1, min($perPage, self::MAX_ITEMS_PER_PAGE));
        $totalPages = (int) ceil($totalItems / $perPage);
        $page = max(1, min($page, $totalPages));

        $response = $response
            ->withHeader('X-Total-Count', $totalItems)
            ->withHeader('X-Total-Pages', $totalPages)
            ->withHeader('X-Current-Page', $page)
            ->withHeader('X-Per-Page', $perPage);

        return $this->jsonResponse($response, [
            'status' => 'success',
            'message' => $message,
            'data' => $this->ensureDataResponseIsArray($data),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalItems,
                'total_pages' => $totalPages,
            ]
        ], $statusCode);
    }

    /**
     * Retorna uma resposta JSON de erro para a API.
     *
     * Exemplo de resposta:
     * ``` json
     *   {
     *   "status": "error",
     *   "message": "Erros de validação encontrados.",
     *   "errors": [
     *     {"field": "email", "message": "O campo email é obrigatório."},
     *     {"field": "senha", "message": "A senha deve ter pelo menos 8 caracteres."}
     *   ]
     * } ```
     *
     * @param Response $response Instância da resposta PSR-7.
     * @param string $message Mensagem de erro.
     * @param ValidationErrorBuilder|array $errors Lista de erros de validação.
     * @param HttpStatusCode $statusCode Código de status HTTP.
     * @return Response
     */
    protected function errorResponse(
        Response $response,
        string $message,
        ValidationErrorBuilder|array $errors = [],
        HttpStatusCode|int $statusCode = HttpStatusCode::BAD_REQUEST
    ): Response
    {
        return $this->jsonResponse($response, [
            'status' => 'error',
            'message' => $message,
            'errors' => $this->formatValidationErrors($errors),
        ], $statusCode);
    }

    protected function multiStatusResponse(
        Response $response,
        string $message,
        ValidationErrorBuilder|array|null $errors,
        array $data,
        HttpStatusCode|int $statusCode = HttpStatusCode::MULTI_STATUS
    ): Response
    {
        return $this->jsonResponse(
            $response,
            [
                'status' => 'multi',
                'message' => $message,
                'errors' => $this->formatValidationErrors($errors),
                'data' => $this->ensureDataResponseIsArray($data)
            ],
            $statusCode
        );
    }

    private function jsonResponse(
        Response $response,
        mixed $data = [],
        HttpStatusCode|int $status = HttpStatusCode::OK
    ): Response
    {
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(is_int($status) ? $status : $status->value);
    }

    private function formatValidationErrors(ValidationErrorBuilder|array $errors): array
    {
        if ($errors instanceof ValidationErrorBuilder) {
            return $errors->build();
        }

        $allErrors = [];

        foreach ($errors as $key => $error) {
            if (is_array($error)) {
                foreach ($error as $subError) {
                    $allErrors[] = [
                        ...(!empty($key) ? ['field' => $key] : []),
                        'message' => $subError
                    ];
                }

                continue;
            }

            $allErrors[] = [
                ...(!empty($key) ? ['field' => $key] : []),
                'message' => $error
            ];
        }

        return $allErrors;
    }

    private function ensureDataResponseIsArray(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        if (!empty($data[0]) && is_array($data[0])) {
            return $data;
        }

        return [$data];
    }
}