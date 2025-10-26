<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Pessoa\Embeddables;

use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CepType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class Endereco
{
    #[ORM\Column(length: 150)]
    public string $rua;

    #[ORM\Column(length: 20)]
    public string $numero;

    #[ORM\Column(length: 100)]
    public string $bairro;

    #[ORM\Column(type: CepType::CEP)]
    public string $cep;
}