<?php

declare(strict_types=1);

namespace App\Application\Contracts\Hashers;

interface PasswordHasherProviderInterface extends HasherProviderInterface
{
    public function hash(string $plainText): string;
    public function isHashed(string $plainTextOrHash): bool;
    public function verify(string $plainText, string $hash): bool;
    public function needsRehash(string $hash): bool;
}