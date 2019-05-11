<?php

namespace Imiskuf\BasicApiBundle\Service\Entity;

use Imiskuf\BasicApiBundle\Exception\Entity\EntityBuildException;
use Imiskuf\BasicApiBundle\Exception\Entity\EntityOperationException;
use Imiskuf\BasicApiBundle\Factory\Entity\EntityFactory;
use Imiskuf\BasicApiBundle\Model\DtoInterface;
use Imiskuf\BasicApiBundle\Model\EntityInterface;
use Doctrine\ORM\EntityManager as BaseEntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;

class EntityManager
{
    /**
     * @var EntityManagerInterface|BaseEntityManager
     */
    private $em;

    /**
     * @var EntityFactory
     */
    private $factory;

    /**
     * @param EntityManagerInterface $objectManager
     * @param EntityFactory $entityFactory
     */
    public function __construct(EntityManagerInterface $objectManager, EntityFactory $entityFactory)
    {
        $this->em = $objectManager;
        $this->factory = $entityFactory;
    }

    /**
     * @param DtoInterface $data
     * @param string $entityClass
     * @return EntityInterface|null
     */
    public function add(DtoInterface $data, string $entityClass): ?EntityInterface
    {
        try {
            $entity = $this->factory->createFromDto($data, $entityClass);

            $this->em->persist($entity);
            $this->em->flush($entity);
        } catch (ORMException | EntityBuildException $e) {
            throw new EntityOperationException('Cannot add entity!', 0, $e);
        }

        return $entity;
    }

    /**
     * @param DtoInterface $data
     * @param EntityInterface $entity
     */
    public function update(DtoInterface $data, EntityInterface $entity): void
    {
        try {
            $entity = $this->factory->updateFromDto($data, $entity);

            $this->em->flush($entity);
        } catch (ORMException | EntityBuildException $e) {
            throw new EntityOperationException('Cannot update entity!', 0, $e);
        }
    }

    /**
     * @param EntityInterface $entity
     */
    public function remove(EntityInterface $entity): void
    {
        try {
            $this->em->remove($entity);
            $this->em->flush($entity);
        } catch (ORMException | EntityBuildException $e) {
            throw new EntityOperationException('Cannot remove entity!', 0, $e);
        }
    }

    /**
     * @param string $repositoryClass
     * @return EntityRepository
     */
    public function getRepository(string $repositoryClass): EntityRepository
    {
        return $this->em->getRepository($repositoryClass);
    }
}
