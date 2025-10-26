<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa\DTOs;

readonly class CoordenadaDTO
{
    public function __construct(
        private ?float $latitude = null,
        private ?float $longitude = null,
    )
    {
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }
}