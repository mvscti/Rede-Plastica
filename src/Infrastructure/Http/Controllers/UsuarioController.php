<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCases\Usuario\DeleteUsuarioUseCase;
use App\Application\UseCases\Usuario\RegisterUsuarioUseCase;
use App\Application\UseCases\Usuario\UpdateUsuarioUseCase;
use App\Domain\Usuario\UsuarioRepositoryInterface;
use App\Infrastructure\Http\Controllers\Helpers\HttpStatusCode;
use App\Infrastructure\Http\Controllers\Helpers\ResponseBuilder;
use App\Infrastructure\Http\Controllers\Helpers\ValidationErrorBuilder;
use App\Infrastructure\Http\Controllers\Helpers\SymfonyValidationErrorHandlerTrait;
use App\Infrastructure\Http\Resources\UsuarioResource;
use App\Infrastructure\Persistence\Queries\QueryParams;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class UsuarioController extends AbstractController
{
    use SymfonyValidationErrorHandlerTrait;

    public function __construct(
        ResponseBuilder                    $responseBuilder,
        ValidationErrorBuilder             $errorBuilder,
        private ValidatorInterface         $validator,
        private UsuarioRepositoryInterface $usuarioRepository,
        private RegisterUsuarioUseCase     $createUsuarioUseCase,
        private UpdateUsuarioUseCase       $updateUsuarioUseCase,
        private DeleteUsuarioUseCase       $deleteUsuarioUseCase
    )
    {
        parent::__construct($responseBuilder, $errorBuilder);
    }

    public function index(Request $request, Response $response): Response
    {
        $paginationParams = $this->extractPaginationParams($request);
        [$page, $perPage] = $paginationParams->toIndexedArray();

        $sortingParams = $this->extractSortingParams($request);
        $filters = $this->extractFilters($request, ['login']);

        $queryParams = new QueryParams()
            ->setPage($page)
            ->setPerPage($perPage)
            ->addFilters($filters)
            ->addOrderByFields($sortingParams->toAssociativeArray());

        $usuarios = $this->usuarioRepository->findAll($queryParams);

        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Lista de usuários recuperada com sucesso.',
                data: UsuarioResource::toResponseCollection($usuarios),
            )->paginate(
                totalItems: $this->usuarioRepository->count($queryParams),
                page: $page,
                perPage: $perPage
            )->withFilters($filters)
            ->withSorts($sortingParams->toAssociativeArray())
            ->build();
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $usuario = $this->usuarioRepository->findById($id);

        if ($usuario === null) {
            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Usuário não encontrado.',
                    statusCode: HttpStatusCode::NOT_FOUND
                )->build();
        }

        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Usuário recuperado com sucesso.',
                data: UsuarioResource::toResponseArray($usuario),
            )->build();
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();

        $this->handleValidationErrors($this->validateUsuarioData($data), $this->errorBuilder);

        if ($this->errorBuilder->hasErrors()) {
            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Erro de validação nos dados fornecidos.',
                    errors: $this->errorBuilder,
                    statusCode: HttpStatusCode::UNPROCESSABLE_ENTITY
                )->build();
        }

        $result = $this->createUsuarioUseCase->create($data['login'], $data['senha']);
        $usuario = $result->getValue();

        if ($result->isFailure() || !$usuario) {
            foreach ($result->getErrors() as $field => $fieldErrors) {
                foreach ((array)$fieldErrors as $error) {
                    $this->errorBuilder->withFieldError($field, $error);
                }
            }

            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Erro ao cadastrar o usuário.',
                    errors: $this->errorBuilder,
                    statusCode: HttpStatusCode::UNPROCESSABLE_ENTITY
                )->build();
        }

        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Usuário criado com sucesso.',
                data: UsuarioResource::toResponseArray($usuario),
                statusCode: HttpStatusCode::CREATED
            )->build();
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $data = (array)$request->getParsedBody();

        $this->handleValidationErrors($this->validateUsuarioData($data, $request->getMethod()), $this->errorBuilder);

        if ($this->errorBuilder->hasErrors()) {
            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Erro de validação nos dados fornecidos.',
                    errors: $this->errorBuilder,
                    statusCode: HttpStatusCode::UNPROCESSABLE_ENTITY
                )->build();
        }

        $result = $this->updateUsuarioUseCase->update($id, $data);
        $usuario = $result->getValue();

        if ($result->isFailure() || !$usuario) {
            $errors = $result->getErrors();

            if (isset($errors['usuario'])) {
                unset($errors['usuario']);
            }

            foreach ($errors as $field => $fieldErrors) {
                foreach ((array)$fieldErrors as $error) {
                    $this->errorBuilder->withFieldError($field, $error);
                }
            }

            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Erro ao atualizar as informações do usuário.',
                    errors: $this->errorBuilder,
                    statusCode: HttpStatusCode::UNPROCESSABLE_ENTITY
                )->build();
        }

        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Usuário atualizado com sucesso.',
                data: UsuarioResource::toResponseArray($usuario),
            )->build();
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int)$args['id'];
        $result = $this->deleteUsuarioUseCase->delete($id);

        if ($result->isFailure()) {
            return $this->responseBuilder
                ->error(
                    response: $response,
                    message: 'Usuário não encontrado e/ou não foi possível apagá-lo.',
                )->build();
        }

        return $this->responseBuilder
            ->success(
                response: $response,
                message: 'Usuário apagado com sucesso.'
            )->build();
    }

    private function validateUsuarioData(array $data, string $method = 'post'): ConstraintViolationListInterface
    {
        $method = strtolower($method);

        $loginConstraints = [
            new Assert\NotBlank(),
            new Assert\Email(),
            new Assert\Length(['min' => 5, 'max' => 255])
        ];
        $senhaConstraints = [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 6, 'max' => 20])
        ];

        if ($method === 'patch') {
            $constraints = [
                'login' => new Assert\Optional($loginConstraints),
                'senha' => new Assert\Optional($senhaConstraints)
            ];
        } else {
            $constraints = [
                'login' => $loginConstraints,
                'senha' => $senhaConstraints
            ];
            $data = array_merge(['login' => '', 'senha' => ''], $data);
        }

        return $this->validator->validate($data, new Assert\Collection($constraints));
    }
}