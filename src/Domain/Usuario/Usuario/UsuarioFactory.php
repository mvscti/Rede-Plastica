<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Usuario;

use App\Domain\Shared\Result;
use App\Domain\Usuario\Usuario\ValueObjects\Email;
use App\Domain\Usuario\Usuario\ValueObjects\Senha;

readonly class UsuarioFactory
{
    /**
     * Cria uma instância de Usuario a partir dos dados fornecidos.
     * Retorna um Result contendo o Usuario ou os erros de validação em caso de falha na validação.
     *
     * @param string $login
     * @param string $senha
     * @param int|null $id
     * @return Result<Usuario> Result contendo o Usuario ou os erros de validação.
     */
    public function create(string $login, string $senha, ?int $id = null): Result
    {
        $result = Result::chainAll([
            'login' => Email::tryValidate($login)
        ]);

        if ($result->isFailure()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(new Usuario(
            login: new Email($login),
            senha: new Senha($senha),
            id: $id
        ));
    }
}