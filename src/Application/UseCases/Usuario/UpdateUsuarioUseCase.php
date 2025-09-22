<?php

declare(strict_types=1);

namespace App\Application\UseCases\Usuario;

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;
use App\Domain\Shared\Result;
use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioFactory;
use App\Domain\Usuario\UsuarioRepositoryInterface;

readonly class UpdateUsuarioUseCase
{
    use SenhaValidationTrait;

    public function __construct(
        private UsuarioRepositoryInterface $repository,
        private UsuarioFactory $usuarioFactory,
        private PasswordHasherProviderInterface $passwordHasherProvider,
    ) {
    }

    /**
     * Atualiza os dados de um usuário existente.
     * Retorna um Result contendo o usuário com os dados atualizados ou os erros de validação em caso de falha.
     * @return Result<Usuario>
     */
    public function update(int $id, array $data): Result
    {
        $usuario = $this->repository->findById($id);

        if (!$usuario) {
            return Result::fail(['usuario' => 'Usuário não encontrado.']);
        }

        $toChain = [];
        $errors = [];

        if (isset($data['senha'])) {
            $senhaValidationResult = $this->validateSenhaPlainText($data['senha']);

            if ($senhaValidationResult->isFailure()) {
                $errors['senha'] = $senhaValidationResult->getErrors();
            } else {
                $hashedSenha = $this->passwordHasherProvider->hash($data['senha']);
                $toChain['senha'] = $usuario->changeSenha($hashedSenha);
            }
        }

        $newLogin = $data['login'] ?? null;
        $newLogin = is_string($newLogin) ? trim($newLogin) : '';

        if (!empty($newLogin)) {
            $toChain['login'] = $usuario->changeLogin($newLogin);
        }

        if (!empty($errors)) {
            return Result::fail($errors);
        }

        $allResults = Result::chainAll($toChain);

        if ($allResults->isFailure()) {
            return Result::fail($allResults->getErrors());
        }

        if ($this->repository->loginExists($newLogin)) {
            return Result::fail(['login' => 'Já existe um usuário cadastrado com este login.']);
        }

        return Result::ok($this->repository->save($usuario));
    }
}