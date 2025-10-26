<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "comprador")]
class CompradorEntity extends UsuarioEntity
{
    #[ORM\Column(name: 'deseja_recomendacao', type: Types::BOOLEAN, options: ['default' => true])]
    public bool $desejaRecomendacao = true {
        get {
            return $this->desejaRecomendacao;
        }
        set {
            $this->desejaRecomendacao = $value;
        }
    }

    public function fromExisting(CompradorEntity $existing): self
    {
        parent::setFieldsFromChild($existing);

        $this->desejaRecomendacao = $existing->desejaRecomendacao;

        return $this;
    }
}