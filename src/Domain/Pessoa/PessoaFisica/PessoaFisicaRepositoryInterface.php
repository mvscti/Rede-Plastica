<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaFisica;

use App\Infrastructure\Persistence\Queries\QueryParams;

interface PessoaFisicaRepositoryInterface
{
    /**
     * Busca todos os usuários.
     * @return PessoaFisica[]
     */
    public function findAll(?QueryParams $queryParams = null): array;

    public function findById(int $id): ?PessoaFisica;

    public function count(?QueryParams $queryParams = null): int;

    public function save(PessoaFisica $pessoaFisica): PessoaFisica;

    public function destroy(int $id): bool;
}