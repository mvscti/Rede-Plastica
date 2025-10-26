<?php

declare(strict_types=1);

namespace App\Domain\Residuo\Plastico;

/**
 * Classificação NBR 13230:2008 para a classificação de resíduos plásticos.
 *
 * A NBR 13230 é uma norma brasileira que estabelece critérios para a classificação
 * de resíduos plásticos com base em seu tipo e composição.
 *
 */
enum ClassificacaoPlasticoNbr13230: int
{
    /**
     * 1 - PET (Polietileno Tereftalato)
     */
    case PET = 1;

    /**
     * 2 - PEAD (Polietileno de Alta Densidade)
     */
    case PEAD = 2;

    /**
     * 3 - PVC (Policloreto de Vinila)
     */
    case PVC = 3;

    /**
     * 4 - PEBD (Polietileno de Baixa Densidade)
     */
    case PEBD = 4;

    /**
     * 5 - PP (Polipropileno)
     */
    case PP = 5; // Polipropileno (5)

    /**
     * 6 - PS (Poliestireno)
     */
    case PS = 6;

    /**
     * 7 - Outros tipos de plástico
     */
    case OUTROS = 7;
}
