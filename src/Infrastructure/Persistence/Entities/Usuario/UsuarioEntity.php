<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario;

use App\Domain\Usuario\Usuario\TipoUsuario;
use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaEntity;
use App\Infrastructure\Persistence\Entities\Usuario\Coletor\ColetorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\TelefoneContatoEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "usuario")]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'tipo', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([
    TipoUsuario::VISITANTE->value => VisitanteEntity::class,
    TipoUsuario::COMPRADOR->value  => CompradorEntity::class,
    TipoUsuario::COLETOR->value  => ColetorEntity::class,
])]
abstract class UsuarioEntity extends BaseEntity
{
    #[ORM\Column(length: 50, unique: true)]
    public string $login;

    #[ORM\Column(length: 255)]
    public string $senha;

    #[ORM\OneToOne(targetEntity: PessoaEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "pessoa_id", referencedColumnName: "id", nullable: false)]
    public PessoaEntity $pessoa;

    /**
     * 0:N - Usuários e Telefones de Contato
     * @var Collection<int, TelefoneContatoEntity>
     */
    #[ORM\OneToMany(targetEntity: TelefoneContatoEntity::class, mappedBy: 'usuario', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $telefones;

    public function __construct()
    {
        $this->telefones = new ArrayCollection();
    }

    protected function setFieldsFromChild(UsuarioEntity $existing): self
    {
        $this->id = $existing->id;
        $this->login = $existing->login;
        $this->senha = $existing->senha;
        $this->pessoa = $existing->pessoa;
        $this->telefones = $existing->telefones;

        return $this;
    }
}