<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaUsuario;

readonly class PessoaUsuarioFactory
{
    /**
     * Cria um novo relacionamento entre Pessoa e Usuário.
     *
     * @param int $pessoaId
     * @param int $usuarioId
     *
     * @return PessoaUsuario
     */
    public function create(int $pessoaId, int $usuarioId): PessoaUsuario
    {
        return new PessoaUsuario($pessoaId, $usuarioId);
    }
}