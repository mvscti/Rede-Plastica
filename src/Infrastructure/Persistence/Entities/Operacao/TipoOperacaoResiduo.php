<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Operacao;

enum TipoOperacaoResiduo: string
{
    case COMPRA = 'compra';
    case COLETA = 'coleta';
    case DOACAO = 'doacao';
}
