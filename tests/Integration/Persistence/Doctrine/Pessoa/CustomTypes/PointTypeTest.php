<?php

use App\Domain\Pessoa\Pessoa\DTOs\CoordenadaDTO;
use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\PointType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

beforeEach(function () {
    $this->platform = $this->createMock(AbstractPlatform::class);
});

it('deve retornar o nome do tipo personalizado', function () {
    $type = new PointType();
    expect($type->getName())->toBe(PointType::POINT);
});

it('deve converter string POINT para o DTO CoordenadaDTO', function () {
    $type = new PointType();

    $result = $type->convertToPHPValue('POINT(1.23 4.56)', $this->platform);
    expect($result)->toEqual(new CoordenadaDTO(4.56, 1.23));
});

it('deve converter o DTO CoordenadaDTO para string POINT', function () {
    $type = new PointType();

    $result = $type->convertToDatabaseValue(new CoordenadaDTO(4.56, 1.23), $this->platform);
    expect($result)->toBe('POINT(1.230000 4.560000)');
});

it('deve retornar null para ponto geográfico nulo', function () {
    $type = new PointType();

    expect($type->convertToPHPValue(null, $this->platform))->toBeNull()
        ->and($type->convertToDatabaseValue(null, $this->platform))->toBeNull();
});