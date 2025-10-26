<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\PessoaJuridica;

use App\Infrastructure\Persistence\Queries\QueryParams;

interface PessoaJuridicaRepositoryInterface
{
    /**
     * Busca todos os usuários.
     * @return PessoaJuridica[]
     */
    public function findAll(?QueryParams $queryParams = null): array;

    public function findById(int $id): ?PessoaJuridica;

    public function count(?QueryParams $queryParams = null): int;

    public function save(PessoaJuridica $pessoaJuridica): PessoaJuridica;

    public function destroy(int $id): bool;
}