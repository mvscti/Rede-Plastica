<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario\Coletor;

use App\Infrastructure\Persistence\Entities\Usuario\Coletor\RedeSocial\RedeSocialEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "coletor")]
class ColetorEntity extends UsuarioEntity
{
    /**
     * 0:N - Usuário e Redes Sociais
     * @var Collection<int, RedeSocialEntity>
     */
    #[ORM\OneToMany(targetEntity: RedeSocialEntity::class, mappedBy: 'coletor', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $redesSociais;

    public function __construct()
    {
        parent::__construct();

        $this->redesSociais = new ArrayCollection();
    }

    public function fromExisting(ColetorEntity $existing): self
    {
        parent::setFieldsFromChild($existing);

        $this->redesSociais = $existing->redesSociais;

        return $this;
    }
}