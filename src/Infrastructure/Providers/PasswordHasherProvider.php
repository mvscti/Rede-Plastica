<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Application\Contracts\Hashers\PasswordHasherProviderInterface;

class PasswordHasherProvider implements PasswordHasherProviderInterface
{
    public function hash(string $plainText): string
    {
        return password_hash($plainText, PASSWORD_DEFAULT);
    }

    public function isHashed(string $plainTextOrHash): bool
    {
        return !!password_get_info($plainTextOrHash);
    }

    public function verify(string $plainText, string $hash): bool
    {
        return password_verify($plainText, $hash);
    }

    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}