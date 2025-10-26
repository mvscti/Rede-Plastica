<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaUsuario;

class PessoaUsuario
{
    public function __construct(
        private int $pessoa_id,
        private int $usuario_id,
    )
    {
    }

    public function getPessoaId(): int
    {
        return $this->pessoa_id;
    }

    public function getUsuarioId(): int
    {
        return $this->usuario_id;
    }
}