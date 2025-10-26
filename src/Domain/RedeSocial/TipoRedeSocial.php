<?php

declare(strict_types=1);

namespace App\Domain\RedeSocial;

enum TipoRedeSocial: string
{
    case FACEBOOK = 'facebook';
    case INSTAGRAM = 'instagram';
    case TIKTOK = 'tiktok';
    case X = 'x';
    case LINKEDIN = 'linkedin';
}