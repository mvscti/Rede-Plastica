<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface ArrayableInterface
{
    /**
     * Converter a entidade em um array
     */
    public function toArray(): array;
}