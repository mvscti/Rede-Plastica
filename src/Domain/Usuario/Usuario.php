<?php

declare(strict_types=1);

namespace App\Domain\Usuario;

use App\Domain\Shared\AbstractDomainEntity;
use App\Domain\Shared\Exceptions\IdAlreadyAssignedException;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use App\Domain\Usuario\ValueObjects\Email;
use App\Domain\Usuario\ValueObjects\Senha;

class Usuario extends AbstractDomainEntity
{
    public function __construct(
        private Email $login,
        private Senha $senha,
        ?int $id
    ) {
        parent::__construct($id);
    }

    public function getLogin(): string
    {
        return $this->login->value();
    }

    public function getSenha(): string
    {
        return $this->senha->value();
    }

    /**
     * Altera o login do usuário.
     * Retorna um Result indicando sucesso ou falha da operação.
     *
     * @param string $newLogin
     * @return Result<bool>
     */
    public function changeLogin(string $newLogin): Result
    {
        $result = Email::tryValidate($newLogin);

        if ($result->isFailure()) {
            return $result;
        }

        if ($newLogin === $this->login->value()) {
            return Result::fail('O novo login deve ser diferente do login atual.');
        }

        $this->login = new Email($newLogin);
        return $result;
    }

    /**
     * Altera a senha do usuário.
     * Retorna um Result indicando sucesso ou falha da operação.
     *
     * @param string $newHashedSenha
     * @return Result<bool>
     */
    public function changeSenha(string $newHashedSenha): Result
    {
        $this->senha = new Senha($newHashedSenha);
        return Result::ok(true);
    }
}