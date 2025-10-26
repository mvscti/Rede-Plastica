<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa;

enum TipoPessoa: string
{
    case FISICA = 'fisica';
    case JURIDICA = 'juridica';
}
