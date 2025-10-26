<?php

declare(strict_types=1);

namespace App\Domain\Pessoa\Pessoa\ValueObjects;

use App\Domain\Pessoa\Pessoa\Exceptions\InvalidCoordenadaException;
use App\Domain\Shared\Result;
use App\Domain\Shared\ValidationResult;
use Stringable;

final readonly class Coordenada implements Stringable
{
    private float $latitude;
    private float $longitude;

    public function __construct(
        float $latitude,
        float $longitude,
    )
    {
        $latitude = round($latitude, 6);
        $longitude = round($longitude, 6);
        $this->validate($latitude, $longitude);

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function equals(Coordenada $other): bool
    {
        return $this->latitude === $other->getLatitude()
            && $this->longitude === $other->getLongitude();
    }

    public function __toString(): string
    {
        return $this->latitude . ',' . $this->longitude;
    }

    /**
     * Tenta validar a Coordenada e retorna um Result.
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return Result<bool>
     */
    public static function tryValidate(float $latitude, float $longitude): Result
    {
        $latitude = round($latitude, 6);
        $longitude = round($longitude, 6);
        $result = new ValidationResult();

        if ((empty($latitude) && $latitude !== 0.0) || (empty($longitude) && $longitude !== 0.0)) {
            $result->addError('Latitude e longitude são obrigatórios.');
        }

        $match = function (float $value): bool {
            return preg_match('/^-?\d+(\.\d+)?$/', (string)$value) === 1;
        };

        if (!$match($latitude)) {
            $result->addError('Latitude inválida. Deve ser um número decimal.');
        }

        if (!$match($longitude)) {
            $result->addError('Longitude inválida. Deve ser um número decimal.');
        }

        if ($latitude < -90 || $latitude > 90) {
            $result->addError('Latitude deve estar entre -90 e 90.');
        }

        if ($longitude < -180 || $longitude > 180) {
            $result->addError('Longitude deve estar entre -180 e 180.');
        }

        if (!$result->isValid()) {
            return Result::fail($result->getErrors());
        }

        return Result::ok(true);
    }

    /**
     * @throws InvalidCoordenadaException
     */
    private function validate(float $latitude, float $longitude): void
    {
        $result = $this::tryValidate($latitude, $longitude);

        if ($result->isFailure()) {
            throw new InvalidCoordenadaException(implode(' ', $result->getErrors()));
        }
    }
}