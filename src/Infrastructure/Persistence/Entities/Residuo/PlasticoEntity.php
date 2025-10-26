<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Residuo;

use App\Domain\Residuo\Plastico\ClassificacaoPlasticoNbr13230;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "plastico")]
class PlasticoEntity extends ResiduoEntity
{
    #[ORM\Column(
        name: 'classificacao_nbr_13230',
        type: Types::SMALLINT,
        enumType: ClassificacaoPlasticoNbr13230::class,
        options: ['comment' => 'Classificação NBR 13230:2008 para a separação de resíduos plásticos.']
    )]
    public ClassificacaoPlasticoNbr13230 $classificacaoNbr13230;
}