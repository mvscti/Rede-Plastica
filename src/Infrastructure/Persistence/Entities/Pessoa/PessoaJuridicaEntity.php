<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Pessoa;

use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CnpjType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "pessoa_juridica")]
class PessoaJuridicaEntity extends PessoaEntity
{
    #[ORM\Column(name: 'razao_social', length: 200)]
    public string $razaoSocial {
        get {
            return $this->razaoSocial;
        }
        set {
            $this->razaoSocial = $value;
        }
    }

    #[ORM\Column(name: 'nome_fantasia', length: 150)]
    public string $nomeFantasia {
        get  {
            return $this->nomeFantasia;
        }
        set {
            $this->nomeFantasia = $value;
        }
    }

    #[ORM\Column(type: CnpjType::CNPJ, unique: true)]
    public string $cnpj {
        get {
            return $this->cnpj;
        }
        set {
            $this->cnpj = $value;
        }
    }

    public function fromExisting(PessoaJuridicaEntity $existing): self
    {
        $this->id = $existing->id;
        $this->razaoSocial = $existing->razaoSocial;
        $this->nomeFantasia = $existing->nomeFantasia;
        $this->cnpj = $existing->cnpj;

        parent::setFieldsFromChild($existing);

        return $this;
    }
}