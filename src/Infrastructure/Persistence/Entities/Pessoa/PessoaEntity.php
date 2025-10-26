<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Pessoa;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Domain\Pessoa\Pessoa\TipoPessoa;
use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\PointType;
use App\Infrastructure\Persistence\Entities\Pessoa\Embeddables\Endereco;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "pessoa")]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'tipo', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([
    TipoPessoa::FISICA->value => PessoaFisicaEntity::class,
    TipoPessoa::JURIDICA->value => PessoaJuridicaEntity::class
])]
abstract class PessoaEntity extends BaseEntity
{
    #[ORM\Embedded(class: Endereco::class, columnPrefix: false)]
    public Endereco $endereco {
        get {
            return $this->endereco;
        }
        set {
            $this->endereco = $value;
        }
    }

    #[ORM\Column(name: 'ponto_geografico', type: PointType::POINT, nullable: true, options: ['default' => null])]
    public ?CoordenadaDTO $pontoGeografico = null {
        get {
            return $this->pontoGeografico;
        }
        set {
            $this->pontoGeografico = $value;
        }
    }

    public function __construct()
    {
        $this->endereco = new Endereco();
    }

    protected function setFieldsFromChild(PessoaEntity $existing): self
    {
        $this->id = $existing->id;
        $this->endereco = $existing->endereco;
        $this->pontoGeografico = $existing->pontoGeografico;

        return $this;
    }
}