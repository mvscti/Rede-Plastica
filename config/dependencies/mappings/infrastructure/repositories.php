<?php

declare(strict_types=1);

use App\Domain\Usuario\UsuarioRepositoryInterface;

use App\Infrastructure\Persistence\Repositories\UsuarioRepository;

use function DI\autowire;

return [
    UsuarioRepositoryInterface::class => autowire(UsuarioRepository::class),
];