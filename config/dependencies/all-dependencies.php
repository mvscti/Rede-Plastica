<?php

declare(strict_types=1);

// Centraliza as dependências do contêiner de injeção de dependências
// para facilitar a manutenção e evitar duplicação de código.
return array_merge(
    require __DIR__ . '/mappings/core.php',

    // Infraestrutura
    require __DIR__ . '/mappings/infrastructure/infrastructure.php',
    require __DIR__ . '/mappings/infrastructure/repositories.php',
);