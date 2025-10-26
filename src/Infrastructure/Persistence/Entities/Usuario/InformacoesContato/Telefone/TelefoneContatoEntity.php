<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone;

use App\Domain\Usuario\InformacoesContato\Telefone\TipoTelefoneContato;
use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\CustomTypes\TelefoneE164Type;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "telefone_contato")]
class TelefoneContatoEntity extends BaseEntity
{
    #[ORM\Column(type: TelefoneE164Type::TELEFONE)]
    public string $telefone;

    #[ORM\Column(type: Types::ENUM, length: 20, enumType: TipoTelefoneContato::class)]
    public TipoTelefoneContato $tipo = TipoTelefoneContato::MOVEL_RESIDENCIAL;

    #[ORM\Column(name: 'whatsapp', type: Types::BOOLEAN)]
    public bool $isWhatsapp = false;

    #[ORM\ManyToOne(targetEntity: UsuarioEntity::class, inversedBy: "telefones")]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public UsuarioEntity $usuario;
}