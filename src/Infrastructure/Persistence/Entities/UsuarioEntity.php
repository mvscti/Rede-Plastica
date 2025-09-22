<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "usuario")]
class UsuarioEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id {
        get {
            return $this->id;
        }
        set {
            $this->id = $value;
        }
    }

    #[ORM\Column(length: 50, unique: true)]
    public string $login {
        get {
            return $this->login;
        }
        set {
            $this->login = $value;
        }
    }

    #[ORM\Column(length: 255)]
    public string $senha {
        get {
            return $this->senha;
        }
        set {
            $this->senha = $value;
        }
    }

    public function fromExisting(UsuarioEntity $existing): self
    {
        $this->id = $existing->id;
        $this->login = $existing->login;
        $this->senha = $existing->senha;
        return $this;
    }
}