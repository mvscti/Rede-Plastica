<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioFactory;
use App\Infrastructure\Persistence\Entities\UsuarioEntity;

readonly class UsuarioMapper
{
    public function __construct(private UsuarioFactory $usuarioFactory)
    {
    }

    public function toDomain(UsuarioEntity $entity): Usuario
    {
        return $this->usuarioFactory->create(
            $entity->login,
            $entity->senha,
            $entity->id,
        )->getValue();
    }

    /**
     * @param array<UsuarioEntity> $entities
     * @return array<Usuario>
     */
    public function mapCollectionToDomain(array $entities): array
    {
        return array_map([$this, 'toDomain'], $entities);
    }

    public function toPersistence(Usuario $usuario): UsuarioEntity
    {
        $entity = new UsuarioEntity();
        $entity->id = $usuario->getId();
        $entity->login = $usuario->getLogin();
        $entity->senha = $usuario->getSenha();
        return $entity;
    }
}