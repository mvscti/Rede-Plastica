<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Exceptions;

use App\Infrastructure\Http\Controllers\AbstractController;
use App\Infrastructure\Http\Controllers\Helpers\HttpStatusCode;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class NotFoundExceptionController extends AbstractController
{
    public function __construct(
        ValidationErrorBuilder $errorBuilder,
        private ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct(errorBuilder: $errorBuilder);
    }

    public function index(Request $request, Exception $exception, bool $displayErrorDetails): Response
    {
        $response = $this->responseFactory->createResponse();

        return $this->errorResponse(
            response: $response,
            message: 'A página que você está procurando não foi encontrada.',
            errors: $this->errorBuilder->withError('Por favor, verifique a URL e tente novamente.'),
            statusCode: HttpStatusCode::NOT_FOUND,
        );
    }
}