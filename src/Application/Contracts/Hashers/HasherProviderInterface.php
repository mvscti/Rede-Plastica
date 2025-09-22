<?php

declare(strict_types=1);

namespace App\Application\Contracts\Hashers;

interface HasherProviderInterface
{
    public function hash(string $plainText): string;
    public function isHashed(string $plainTextOrHash): bool;
}