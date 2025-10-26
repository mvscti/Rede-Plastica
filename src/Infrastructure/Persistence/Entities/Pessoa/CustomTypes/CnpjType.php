<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;

class CnpjType extends Type
{
    public const string CNPJ = 'cnpj';

    public function getName(): string
    {
        return self::CNPJ;
    }

    public static function removeFormatting(string $value): string
    {
        return str_replace(['.', '/', '-'], '', $value);
    }

    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 14, 'fixed' => true]);
    }

    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return self::removeFormatting($value);
    }

    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): string
    {
        return $value;
    }
}