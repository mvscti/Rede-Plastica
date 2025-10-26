<?php

use App\Infrastructure\Persistence\Entities\Usuario\InformacoesContato\Telefone\CustomTypes\TelefoneE164Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

beforeEach(function () {
    $this->platform = $this->createMock(AbstractPlatform::class);
});

it('deve retornar o nome do tipo personalizado', function () {
    expect(new TelefoneE164Type()->getName())->toBe(TelefoneE164Type::TELEFONE);
});

it('deve converter string TelefoneE164 para tipo primitivo string', function (string $value) {
    $result = new TelefoneE164Type()->convertToPHPValue($value, $this->platform);
    expect($result)->toBe($value);
})->with(['32912345678', '3212345678']);

it('deve remover a formatação do TelefoneE164 corretamente', function (string $value, string $expected) {
    expect(TelefoneE164Type::removeFormatting($value))->toBe($expected);
})->with([
    ['value' => '(11) 91234-5678', 'expected' => '11912345678'],
    ['value' => '(32) 9 9999-9999', 'expected' => '32999999999'],
    ['value' => '32 9 9999-9999', 'expected' => '32999999999'],
    ['value' => '32-9-9999-9999', 'expected' => '32999999999'],
    ['value' => '32.9.9999.9999', 'expected' => '32999999999'],
    ['value' => '32999999999', 'expected' => '32999999999'],
]);

it('deve converter a string TelefoneE164 para ser armazenada no banco', function (
    string $value, string $expected
) {
    $result = new TelefoneE164Type()->convertToDatabaseValue($value, $this->platform);
    expect($result)->toBe(TelefoneE164Type::removeFormatting($expected));
})->with([
    ['value' => '(11) 91234-5678', 'expected' => '5511912345678'],
    ['value' => '(11) 1234-5678', 'expected' => '5511912345678'],
    ['value' => '(32) 9 9999-9999', 'expected' => '5532999999999'],
    ['value' => '32 9 9999-9999', 'expected' => '5532999999999'],
    ['value' => '32 9999-9999', 'expected' => '5532999999999'],
    ['value' => '32-9-9999-9999', 'expected' => '5532999999999'],
    ['value' => '32-9999-9999', 'expected' => '5532999999999'],
    ['value' => '32.9.9999.9999', 'expected' => '5532999999999'],
    ['value' => '32.9999.9999', 'expected' => '5532999999999'],
    ['value' => '32999999999', 'expected' => '5532999999999'],
    ['value' => '3299999999', 'expected' => '5532999999999'],
]);