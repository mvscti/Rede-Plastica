<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\CustomTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Override;

class TelefoneE164Type extends Type
{
    public const string TELEFONE = 'telefone';

    public function getName(): string
    {
        return self::TELEFONE;
    }

    public static function removeFormatting(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([
            'length' => 13,
            'fixed' => true,
            'nullable' => $column['nullable'] ?? true
        ]);
    }

    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return !empty($value) ? $this->toE164WithoutPlus($value) : null;
    }

    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return $value;
    }

    // Padrão E.164 (da UIT) sem o símbolo de mais (+)
    private function toE164WithoutPlus(string $phoneNumber): string
    {
        $phoneNumber = self::removeFormatting($phoneNumber);

        if (empty($phoneNumber)) {
            return '';
        }

        if (preg_match('/^(\d{2})(\d{8,9})$/', $phoneNumber, $matches)) {
            [ , $ddd, $subscriberNumber ] = $matches;

            if (strlen($subscriberNumber) === 8) {
                $subscriberNumber = '9' . $subscriberNumber;
            }

            $phoneNumber = $ddd . $subscriberNumber;
        }

        if (strlen($phoneNumber) === 11) {
            $phoneNumber = '55' . $phoneNumber;
        }

        return $phoneNumber;
    }
}