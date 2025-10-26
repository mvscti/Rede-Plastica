<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence\Doctrine\Usuario;

use App\Domain\Usuario\Usuario\TipoUsuario;
use App\Infrastructure\Persistence\Entities\Pessoa\PessoaFisicaEntity;
use App\Infrastructure\Persistence\Entities\Usuario\Coletor\ColetorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\CompradorEntity;
use App\Infrastructure\Persistence\Entities\Usuario\UsuarioEntity;
use App\Infrastructure\Persistence\Entities\Usuario\VisitanteEntity;

trait UsuarioTestHelpersTrait
{
    public function createAndPersistMinimalColetorEntity(): ColetorEntity
    {
        return $this->createAndPersistMinimalUsuarioEntity(TipoUsuario::COLETOR);
    }

    public function createAndPersistMinimalCompradorEntity(): CompradorEntity
    {
        return $this->createAndPersistMinimalUsuarioEntity(TipoUsuario::COMPRADOR);
    }

    public function createAndPersistMinimalVisitanteEntity(): VisitanteEntity
    {
        return $this->createAndPersistMinimalUsuarioEntity();
    }

    public function createVisitanteEntity(string $login, string $senha): VisitanteEntity
    {
        $visitante = new VisitanteEntity();
        $visitante->login = $login;
        $visitante->senha = password_hash($senha, PASSWORD_DEFAULT);
        $visitante->pessoa = test()->createMinimalPessoaFisicaEntity();
        return $visitante;
    }

    public function createMinimalPessoaFisicaEntity(): PessoaFisicaEntity
    {
        $randomCpf = (string) rand(10000000000, 99999999999);

        return test()->createPessoaFisicaEntity(
            'Usuário Isolado',
            $randomCpf,
            '1990-01-01',
            ['Rua Isolada', '123', 'Bairro Isolado', '12345678']
        );
    }

    private function createAndPersistMinimalUsuarioEntity(TipoUsuario $type = TipoUsuario::VISITANTE): CompradorEntity | ColetorEntity | VisitanteEntity
    {
        $usuario = match ($type) {
            TipoUsuario::COMPRADOR => new CompradorEntity(),
            TipoUsuario::COLETOR => new ColetorEntity(),
            default => new VisitanteEntity(),
        };

        $usuario->login = 'usuario_isolado@testes.integracao';
        $usuario->senha = password_hash('senha123', PASSWORD_DEFAULT);
        $usuario->pessoa = $this->createMinimalPessoaFisicaEntity();

        test()->persistAndFlush($usuario);
        return $usuario;
    }
}