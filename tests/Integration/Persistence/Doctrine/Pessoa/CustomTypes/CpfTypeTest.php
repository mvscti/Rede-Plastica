<?php

use App\Infrastructure\Persistence\Entities\Pessoa\CustomTypes\CpfType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

beforeEach(function () {
    $this->platform = $this->createMock(AbstractPlatform::class);
});

it('deve retornar o nome do tipo personalizado', function () {
    $type = new CpfType();
    expect($type->getName())->toBe(CpfType::CPF);
});

it('deve converter string CPF para tipo primitivo string', function () {
    $type = new CpfType();

    $result = $type->convertToPHPValue('12345678910', $this->platform);
    expect($result)->toBe('12345678910');
});

it('deve remover a formatação do CPF corretamente', function (string $value, string $expected) {
    expect(CpfType::removeFormatting($value))->toBe($expected);
})->with([
    ['value' => '123.456.789-10', 'expected' => '12345678910'],
    ['value' => '12345678910', 'expected' => '12345678910'],
    ['value' => '', 'expected' => ''],
]);

it('deve converter a string CPF para ser armazenada no banco', function (string $cpf) {
    $type = new CpfType();

    $result = $type->convertToDatabaseValue($cpf, $this->platform);
    expect($result)->toBe(CpfType::removeFormatting($cpf));
})->with(['cpf_formatado' => '123.456.789-10', 'cpf_sem_formatacao' => '12345678910']);