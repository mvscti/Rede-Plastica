<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario\Coletor\RedeSocial;

use App\Domain\RedeSocial\TipoRedeSocial;
use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Usuario\Coletor\ColetorEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "rede_social")]
class RedeSocialEntity extends BaseEntity
{
    #[ORM\Column(type: Types::ENUM, length: 20, enumType: TipoRedeSocial::class)]
    public TipoRedeSocial $tipo;

    #[ORM\Column(length: 255)]
    public string $profile;

    #[ORM\ManyToOne(targetEntity: ColetorEntity::class, inversedBy: "redesSociais")]
    #[ORM\JoinColumn(name: 'coletor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public ColetorEntity $coletor;
}