<?php

declare(strict_types=1);

namespace App\Domain\Usuario\InformacoesContato\Telefone;

enum TipoTelefoneContato: string
{
    case FIXO_COMERCIAL = 'fixo_comercial';
    case FIXO_RESIDENCIAL = 'fixo_residencial';
    case MOVEL_COMERCIAL = 'móvel_comercial';
    case MOVEL_RESIDENCIAL = 'móvel_residencial';
}
