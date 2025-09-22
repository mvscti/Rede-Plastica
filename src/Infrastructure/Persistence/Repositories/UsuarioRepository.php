<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Usuario\Exceptions\EmailAlreadyRegisteredException;
use App\Domain\Usuario\Usuario;
use App\Domain\Usuario\UsuarioRepositoryInterface;
use App\Infrastructure\Persistence\Entities\UsuarioEntity;
use App\Infrastructure\Persistence\Mappers\UsuarioMapper;
use App\Infrastructure\Persistence\Queries\QueryParams;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

readonly class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UsuarioMapper $mapper
    ) {
    }

    public function findAll(?QueryParams $queryParams = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(UsuarioEntity::class, 'u');

        foreach ($queryParams->getFilters() as $field => $value) {
            $qb->andWhere("u.$field = :$field")
                ->setParameter($field, $value);
        }

        foreach ($queryParams->getOrderBy() as $field => $direction) {
            $qb->addOrderBy("u.$field", $direction);
        }

        $qb->setFirstResult($queryParams->getOffset())->setMaxResults($queryParams->getPerPage());

        $result = $qb->getQuery()->getResult();

        return $this->mapper->mapCollectionToDomain($result);
    }

    public function findById(int $id): ?Usuario
    {
        $usuario = $this->em->getRepository(UsuarioEntity::class)->find($id);
        return $usuario ? $this->mapper->toDomain($usuario) : null;
    }

    public function findByLogin(string $login): ?Usuario
    {
        $usuario = $this->em->getRepository(UsuarioEntity::class)->findOneBy(['login' => $login]);
        return $usuario ? $this->mapper->toDomain($usuario) : null;
    }

    public function loginExists(string $login): bool
    {
        return (bool) $this->em->createQueryBuilder()
            ->select('1')
            ->from(UsuarioEntity::class, 'u')
            ->where('u.login = :login')
            ->setParameter('login', $login)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function count(?QueryParams $queryParams = null): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(UsuarioEntity::class, 'u');

        foreach ($queryParams->getFilters() as $field => $value) {
            $qb->andWhere("u.$field = :$field")
                ->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function save(Usuario $usuario): Usuario
    {
        $usuarioEntity = $this->mapper->toPersistence($usuario);

        try {
            if ($usuario->getId()) {
                $this->em->getRepository(UsuarioEntity::class)->find($usuario->getId())->fromExisting($usuarioEntity);
            } else {
                $this->em->persist($usuarioEntity);
            }

            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new EmailAlreadyRegisteredException('Já existe um usuário cadastrado com este login.', 0, $e);
        }

        if (!$usuario->getId()) {
            $usuario->assignId($usuarioEntity->id);
        }

        return $usuario;
    }

    public function destroy(int $id): bool
    {
        $usuario = $this->em->getRepository(UsuarioEntity::class)->find($id);

        if ($usuario === null) {
            return false;
        }

        $this->em->remove($usuario);
        $this->em->flush();

        return true;
    }
}