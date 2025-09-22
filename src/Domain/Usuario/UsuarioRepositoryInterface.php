<?php

declare(strict_types=1);

namespace App\Domain\Usuario;

use App\Infrastructure\Persistence\Queries\QueryParams;

interface UsuarioRepositoryInterface
{
    /**
     * Busca todos os usuários.
     * @return Usuario[]
     */
    public function findAll(?QueryParams $queryParams = null): array;

    public function findById(int $id): ?Usuario;

    public function findByLogin(string $login): ?Usuario;

    public function loginExists(string $login): bool;

    public function count(?QueryParams $queryParams = null): int;

    public function save(Usuario $usuario): Usuario;

    public function destroy(int $id): bool;
}