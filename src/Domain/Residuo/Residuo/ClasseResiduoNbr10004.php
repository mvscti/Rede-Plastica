<?php

declare(strict_types=1);

namespace App\Domain\Residuo\Residuo;

use Deprecated;

/**
 * Classe NBR 10004:2024 para classificação de resíduos sólidos.
 *
 * A NBR 10004 é uma norma brasileira que estabelece critérios para a classificação
 * de resíduos sólidos com base em suas características de periculosidade.
 *
 * Nota: As subclasses II A e II B foram descontinuadas em versões mais recentes da norma,
 *  e agora todos os resíduos não perigosos são simplesmente classificados como Classe II.
 */
enum ClasseResiduoNbr10004: string
{
    /**
     * Refere-se a resíduos que apresentam características de periculosidade,
     * como inflamabilidade, corrosividade, reatividade, toxicidade ou patogenicidade.
     * Exemplos incluem resíduos químicos, resíduos hospitalares, resíduos radioativos
     * e certos tipos de resíduos industriais.
     */
    case CLASSE_I = 'I'; // Perigosos

    /**
     * Refere-se a resíduos não perigosos, incluindo resíduos sólidos urbanos (RSU),
     * resíduos de construção e demolição (RCD), resíduos industriais não perigosos,
     * resíduos agrícolas e resíduos de serviços de saúde não perigosos.
     */
    case CLASSE_II = 'II'; // Não perigosos

    /**
     * @deprecated Use CLASSE_II no lugar de CLASSE_IIA.
     *  A norma NBR 10004 foi atualizada e a subdivisão II A foi eliminada. (NBR 10004:2024)
     */
    #[Deprecated(message: 'Use CLASSE_II no lugar de CLASSE_IIA')]
    case CLASSE_IIA = 'IIA';

    /**
     * @deprecated Use CLASSE_II no lugar de CLASSE_IIB.
     *  A norma NBR 10004 foi atualizada e a subdivisão II A foi eliminada. (NBR 10004:2024)
     */
    #[Deprecated(message: 'Use CLASSE_II no lugar de CLASSE_IIB')]
    case CLASSE_IIB = 'IIB';
}