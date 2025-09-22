<?php

declare(strict_types=1);

namespace App\Application\UseCases\Usuario;

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioFactory;
use App\Domain\Usuario\UsuarioRepositoryInterface;

readonly class RegisterUsuarioUseCase
{
    use SenhaValidationTrait;

    public function __construct(
        private UsuarioRepositoryInterface $repository,
        private UsuarioFactory $usuarioFactory,
        private PasswordHasherProviderInterface $passwordHasherProvider
    ) {
    }

    /**
     * Cria um novo usuário.
     * Retorna um Result contendo o usuário criado ou os erros de validação em caso de falha.
     * @return Result<Usuario>
     */
    public function create(string $login, string $senhaPlainText): Result
    {
        $senhaValidationResult = $this->validateSenhaPlainText($senhaPlainText);

        if ($senhaValidationResult->isFailure()) {
            return Result::fail(['senha' => $senhaValidationResult->getErrors()]);
        }

        $hashedSenha = $this->passwordHasherProvider->hash($senhaPlainText);
        $result = $this->usuarioFactory->create($login, $hashedSenha);

        if ($result->isFailure()) {
            return Result::fail($result->getErrors());
        }

        if ($this->repository->loginExists($login)) {
            return Result::fail(['login' => 'Já existe um usuário cadastrado com este login.']);
        }

        return Result::ok($this->repository->save($result->getValue()));
    }
}