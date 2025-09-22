<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class HomeController extends AbstractController
{
    public function index(Request $request, Response $response): Response
    {
        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Bem-vindo(a) à API - Rede Plástica!',
                data: [
                    'documentation' => 'À ser definida.',
                ]
            )->build();
    }
}