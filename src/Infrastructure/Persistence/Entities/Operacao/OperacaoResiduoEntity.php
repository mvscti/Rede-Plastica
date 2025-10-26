<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Operacao;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "usuario_residuo")]
class OperacaoResiduoEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'usuario_id', type: Types::INTEGER)]
    public ?int $usuarioId {
        get {
         return $this->usuarioId;
        }
        set {
         $this->usuarioId = $value;
        }
    }

    #[ORM\Id]
    #[ORM\Column(name: 'residuo_id', type: Types::INTEGER)]
    public ?int $residuoId {
        get {
            return $this->residuoId;
        }
        set {
            $this->residuoId = $value;
        }
    }

    #[ORM\Column(name: 'tipo_operacao', type: Types::ENUM, enumType: TipoOperacaoResiduo::class)]
    public TipoOperacaoResiduo $tipoOperacao {
        get {
            return $this->tipoOperacao;
        }
        set {
            $this->tipoOperacao = $value;
        }
    }
}