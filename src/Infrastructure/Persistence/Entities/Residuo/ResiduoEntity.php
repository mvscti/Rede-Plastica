<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Residuo;

use App\Domain\Residuo\Residuo\ClasseResiduoNbr10004;
use App\Domain\Residuo\Residuo\TipoResiduo;
use App\Infrastructure\Persistence\Entities\BaseEntity;
use App\Infrastructure\Persistence\Entities\Residuo\Imagem\ImagemEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "residuo")]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'tipo', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([TipoResiduo::PLASTICO->value => PlasticoEntity::class])]
abstract class ResiduoEntity extends BaseEntity
{
    #[ORM\Column(
        name: 'classe_nbr_10004',
        type: Types::ENUM,
        length: 3,
        enumType: ClasseResiduoNbr10004::class,
        options: ['comment' => 'Classe NBR 10004:2024 para classificação de resíduos sólidos.']
    )]
    public ClasseResiduoNbr10004 $classeNbr10004;

    #[ORM\Column(length: 50)]
    public string $cor;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    public float $preco;

    #[ORM\Column(name: 'qtd', type: Types::INTEGER)]
    public int $quantidade;

    /**
     * 1:N - Resíduo e Imagens
     * @var Collection<int, ImagemEntity>
     */
    #[ORM\OneToMany(targetEntity: ImagemEntity::class, mappedBy: 'residuo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $imagens;

    public function __construct()
    {
        $this->imagens = new ArrayCollection();
    }
}