<?php

use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CnpjType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

beforeEach(function () {
    $this->platform = $this->createMock(AbstractPlatform::class);
});

it('deve retornar o nome do tipo personalizado', function () {
    $type = new CnpjType();
    expect($type->getName())->toBe(CnpjType::CNPJ);
});

it('deve converter string CNPJ para tipo primitivo string', function () {
    $type = new CnpjType();

    $result = $type->convertToPHPValue('12345678000199', $this->platform);
    expect($result)->toBe('12345678000199');
});

it('deve remover a formatação do CNPJ corretamente', function (string $value, string $expected) {
    expect(CnpjType::removeFormatting($value))->toBe($expected);
})->with([
    ['value' => '12.345.678/0001-99', 'expected' => '12345678000199'],
    ['value' => '12345678000199', 'expected' => '12345678000199'],
    ['value' => '1A.23B.45C/678D-90', 'expected' => '1A23B45C678D90'],
    ['value' => '1A23B45C678D90', 'expected' => '1A23B45C678D90'],
    ['value' => '', 'expected' => ''],
]);

it('deve converter a string CNPJ para ser armazenada no banco', function (string $cnpj) {
    $type = new CnpjType();

    $result = $type->convertToDatabaseValue($cnpj, $this->platform);
    expect($result)->toBe(CnpjType::removeFormatting($cnpj));
})->with(['cnpj_formatado' => '12.345.678/0001-99', 'cnpj_sem_formatacao' => '12345678000199']);