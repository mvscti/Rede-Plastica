<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middlewares;

use App\Infrastructure\Http\Controllers\HttpExceptions\InternalServerErrorExceptionController;
use App\Infrastructure\Http\Controllers\HttpExceptions\MethodNotAllowedExceptionController;
use App\Infrastructure\Http\Controllers\HttpExceptions\NotFoundExceptionController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;

readonly class HttpExceptionHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException) {
            return $this->container->get(NotFoundExceptionController::class)->index();
        } catch (HttpMethodNotAllowedException) {
            return $this->container->get(MethodNotAllowedExceptionController::class)->index();
        } catch (\Throwable) {
            if ($this->container->get('config')['app']['display_errors'] ?? false) {
                return $handler->handle($request);
            }

            return $this->container->get(InternalServerErrorExceptionController::class)->index();
        }
    }
}