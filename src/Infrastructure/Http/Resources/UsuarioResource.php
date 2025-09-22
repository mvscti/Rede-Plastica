<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources;

use App\Domain\Usuario\Usuario;

class UsuarioResource
{
    public static function toResponseArray(Usuario $usuario): array
    {
        return [
            'id' => $usuario->getId(),
            'login' => $usuario->getLogin()
        ];
    }

    /**
     * @param array<Usuario> $collection
     * @return array<array<string, mixed>>
     */
    public static function toResponseCollection(array $collection): array
    {
        return array_map(
            fn (Usuario $usuario) => self::toResponseArray($usuario),
            $collection
        );
    }
}