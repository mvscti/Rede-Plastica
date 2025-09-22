<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\HttpExceptions;

use App\Infrastructure\Http\Controllers\AbstractController;
use App\Infrastructure\Http\Controllers\Helpers\HttpStatusCode;
use App\Infrastructure\Http\Controllers\Helpers\ResponseBuilder;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class MethodNotAllowedExceptionController extends AbstractController
{
    public function __construct(
        ResponseBuilder $responseBuilder,
        ValidationErrorBuilder $errorBuilder,
        private ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($responseBuilder, $errorBuilder);
    }

    public function index(): Response
    {
        $response = $this->responseFactory->createResponse();

        return $this->responseBuilder->error(
            response: $response,
            message: 'O método HTTP utilizado não é permitido para esta rota.',
            errors: $this->errorBuilder->withError('Por favor, verifique os métodos permitidos.'),
            statusCode: HttpStatusCode::METHOD_NOT_ALLOWED,
        )->build();
    }
}