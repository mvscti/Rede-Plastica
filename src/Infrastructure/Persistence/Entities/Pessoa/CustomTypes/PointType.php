<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes;

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;

class PointType extends Type
{
    public const string POINT = 'point';

    public function getName(): string
    {
        return self::POINT;
    }

    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'geometry(POINT, 4326)';
    }

    /**
     * @return array{longitude: float, latitude: float}|null
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?CoordenadaDTO
    {
        // Se já for WKT (padrão Well-Known Text -> "POINT(longitude latitude)" = "POINT(x y)")
        if (is_string($value) && preg_match('/POINT\(([-\d.]+) ([-\d.]+)\)/', $value, $matches)) {
            return new CoordenadaDTO((float)$matches[2], (float)$matches[1]);
        }

        return null;
    }

    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!($value instanceof CoordenadaDTO)) {
            return null;
        }

        return sprintf('POINT(%F %F)', $value->getLongitude(), $value->getLatitude());
    }

    #[Override]
    public function convertToPHPValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    #[Override]
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_GeomFromText(%s, 4326)', $sqlExpr);
    }
}