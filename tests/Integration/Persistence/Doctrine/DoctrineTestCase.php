<?php

declare(strict_types=1);

namespace Tests\Integration\Persistence\Doctrine;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Integration\IntegrationTestCase;

abstract class DoctrineTestCase extends IntegrationTestCase
{
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->getEntityManager();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if (!isset($this->em) || !$this->em->isOpen()) {
            /** @var EntityManagerInterface $em */
            $em = self::getContainer()->get(EntityManagerInterface::class);
            $this->em = $em;
        }

        return $this->em;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->em) && $this->em->isOpen()) {
            $this->em->close();
        }

        unset($this->em);
    }

    public function persistAndFlush($entity, bool $withClear = false): void
    {
        $this->em->persist($entity);
        $this->em->flush();

        if ($withClear) {
            $this->em->clear();
        }
    }

    /**
     * Persiste a entidade, faz flush e retorna a entidade recarregada do banco de dados.
     *
     * @template T of object Tipo genérico para a entidade
     * @param T $entity A entidade a ser persistida
     * @param string $idKey O nome da propriedade que representa a chave primária (padrão é 'id')
     * @param bool $withClear Se deve limpar o EntityManager após o flush (padrão é true)
     * @return T|null A entidade recarregada do banco de dados ou null se não encontrada
     */
    public function persistAndFlushWithFind(mixed $entity, string $idKey = 'id', bool $withClear = true)
    {
        $idKey = trim($idKey);

        if (empty($idKey) || !property_exists($entity, $idKey)) {
            $idKey = 'id';
        }

        $this->persistAndFlush($entity, $withClear);
        return $this->em->getRepository(get_class($entity))->find($entity->$idKey);
    }

    public function removeAndFlush($entity, bool $withClear = false): void
    {
        $this->em->remove($entity);
        $this->em->flush();

        if ($withClear) {
            $this->em->clear();
        }
    }

    protected function cleanTablesFromEntities(array $entities): void
    {
        $tables = array_map(fn($entity) => $this->em->getClassMetadata($entity)->getTableName(), $entities);
        $this->cleanTables($tables);
    }

    private function cleanTables(array $tables): void
    {
        $connection = $this->em->getConnection();

        try {
            $platform = $connection->getDatabasePlatform();
            $connection->beginTransaction();

            foreach ($tables as $table) {
                $connection->executeStatement($platform->getTruncateTableSQL($table, true));
            }

            $connection->commit();
        } catch (Exception) {
            try {
                $connection->rollBack();
            } catch (Exception) {
            }
        }
    }
}
