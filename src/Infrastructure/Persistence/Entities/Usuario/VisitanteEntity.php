<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "visitante")]
class VisitanteEntity extends UsuarioEntity
{
    public function fromExisting(UsuarioEntity $existing): self
    {
        parent::setFieldsFromChild($existing);

        return $this;
    }
}