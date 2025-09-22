<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Helpers;

use Psr\Http\Message\ResponseInterface;

class ResponseBuilder
{
    private const int DEFAULT_MAX_ITEMS_PER_PAGE = 10;

    private int $maxItemsPerPage = self::DEFAULT_MAX_ITEMS_PER_PAGE;

    private ResponseInterface $response;

    private array $responseBody = [];

    public function changeMaxItemsPerPage(int $maxItemsPerPage): self
    {
        $this->maxItemsPerPage = max($maxItemsPerPage, 1);
        return $this;
    }

    public function withResponse(ResponseInterface $response): self
    {
        $this->response = $response;
        return $this;
    }

    public function set(string $key, mixed $value): self
    {
        $this->responseBody[$key] = $value;
        return $this;
    }

    public function success(
        ResponseInterface $response,
        string $message,
        array $data = [],
        HttpStatusCode|int $statusCode = HttpStatusCode::OK
    ): self
    {
        $this->response = $this->withResponse($response)->responseWithStatusCode($statusCode);

        return $this->set('status', 'success')
            ->set('message', $message)
            ->set('data', $this->ensureDataResponseIsArray($data));
    }

    public function error(
        ResponseInterface $response,
        string $message,
        ValidationErrorBuilder|array $errors = [],
        HttpStatusCode|int $statusCode = HttpStatusCode::BAD_REQUEST
    ): self
    {
        $this->response = $this->withResponse($response)->responseWithStatusCode($statusCode);

        return $this->set('status', 'error')
            ->set('message', $message)
            ->set('errors', $this->formatValidationErrors($errors));
    }

    public function multi(
        ResponseInterface $response,
        string $message,
        ValidationErrorBuilder|array|null $errors,
        array $data,
        HttpStatusCode|int $statusCode = HttpStatusCode::MULTI_STATUS
    ): self
    {
        $this->response = $this->withResponse($response)->responseWithStatusCode($statusCode);

        return $this->set('status', 'multi')
            ->set('message', $message)
            ->set('errors', $this->formatValidationErrors($errors ?? []))
            ->set('data', $this->ensureDataResponseIsArray($data));
    }

    public function paginate(int $totalItems, int $page = 1, int $perPage = 10): self
    {
        $perPage = max(1, min($perPage, $this->maxItemsPerPage));
        $totalPages = (int) ceil($totalItems / $perPage);
        $page = max(1, $page);

        $this->response = $this->response
            ->withHeader('X-Total-Count', $totalItems)
            ->withHeader('X-Total-Pages', $totalPages)
            ->withHeader('X-Current-Page', $page)
            ->withHeader('X-Per-Page', $perPage);

        $this->responseBody['meta']['pagination'] = [
            'total_items' => $totalItems,
            'current_page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];

        return $this;
    }

    public function withFilters(array $filters): self
    {
        $this->responseBody['meta']['filters'] = $filters;
        return $this;
    }

    public function withSorts(array $sorts): self
    {
        $this->responseBody['meta']['sorts'] = $sorts;
        return $this;
    }

    public function setMeta(array $meta): self
    {
        $this->responseBody['meta'] = array_merge($this->responseBody['meta'] ?? [], $meta);
        return $this;
    }

    public function build(): ResponseInterface
    {
        $this->response = $this->response
            ->withHeader('Content-Type', 'application/json');

        $this->response->getBody()
            ->write(json_encode($this->responseBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $this->response;
    }

    private function responseWithStatusCode(HttpStatusCode|int $statusCode): ResponseInterface
    {
        return $this->response = $this->response
            ->withStatus(is_int($statusCode) ? $statusCode : $statusCode->value);
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
}
