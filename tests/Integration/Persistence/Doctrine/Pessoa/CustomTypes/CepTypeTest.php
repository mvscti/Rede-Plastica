<?php

use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CepType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

beforeEach(function () {
    $this->platform = $this->createMock(AbstractPlatform::class);
});

it('deve retornar o nome do tipo personalizado', function () {
    $type = new CepType();
    expect($type->getName())->toBe(CepType::CEP);
});

it('deve converter string CEP para tipo primitivo string', function () {
    $type = new CepType();

    $result = $type->convertToPHPValue('12356789', $this->platform);
    expect($result)->toBe('12356789');
});

it('deve remover a formatação do CEP corretamente', function (string $value, string $expected) {
    expect(CepType::removeFormatting($value))->toBe($expected);
})->with([
    ['value' => '12356-789', 'expected' => '12356789'],
    ['value' => '12356789', 'expected' => '12356789'],
    ['value' => '12.356-789', 'expected' => '12356789'],
    ['value' => '12.356.789', 'expected' => '12356789'],
    ['value' => '12.356.78-9', 'expected' => '12356789'],
]);

it('deve converter a string CEP para ser armazenada no banco', function (string $cep) {
    $type = new CepType();

    $result = $type->convertToDatabaseValue($cep, $this->platform);
    expect($result)->toBe(CepType::removeFormatting($cep));
})->with(['cep_formatado' => '12356-789', 'cep_sem_formatacao' => '12356789']);