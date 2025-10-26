<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Residuo\Imagem;

use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Residuo\ResiduoEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "imagem")]
class ImagemEntity extends BaseEntity
{
    #[ORM\Column(type: Types::TEXT)]
    public string $url;

    #[ORM\ManyToOne(targetEntity: ResiduoEntity::class, inversedBy: "imagens")]
    #[ORM\JoinColumn(name: 'residuo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ResiduoEntity $residuo;
}