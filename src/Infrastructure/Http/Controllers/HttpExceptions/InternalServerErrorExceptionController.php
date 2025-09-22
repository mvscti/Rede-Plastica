<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\HttpExceptions;

use App\Infrastructure\Http\Controllers\AbstractController;
use App\Infrastructure\Http\Controllers\Helpers\HttpStatusCode;
use App\Infrastructure\Http\Controllers\Helpers\ResponseBuilder;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;

final readonly class InternalServerErrorExceptionController extends AbstractController
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
            message: 'Ocorreu um erro interno no servidor.',
            errors: $this->errorBuilder->withError('Por favor, tente novamente mais tarde.'),
            statusCode: HttpStatusCode::INTERNAL_SERVER_ERROR,
        )->build();
    }
}