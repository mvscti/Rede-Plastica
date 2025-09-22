<?php

declare(strict_types=1);

namespace App\Application\UseCases\Usuario;

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Domain\Shared\Result;
use App\Domain\Usuario\UsuarioFactory;
use App\Domain\Usuario\UsuarioRepositoryInterface;

readonly class DeleteUsuarioUseCase
{
    use SenhaValidationTrait;

    public function __construct(
        private UsuarioRepositoryInterface $repository,
        private UsuarioFactory $usuarioFactory,
        private PasswordHasherProviderInterface $passwordHasherProvider
    ) {
    }

    /**
     * Apaga um usuário.
     * Retorna um Result com true se o usuário foi apagado com sucesso ou um Result com erro caso contrário.
     * @return Result<bool>
     */
    public function delete(int $id): Result
    {
        $hasDeleted = $this->repository->destroy($id);

        if (!$hasDeleted) {
            return Result::fail('Usuário não encontrado e/ou não foi possível apagá-lo.');
        }

        return Result::ok(true);
    }
}