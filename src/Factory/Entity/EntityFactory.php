<?php

namespace Imiskuf\BasicApiBundle\Factory\Entity;

use Imiskuf\BasicApiBundle\Exception\Entity\EntityBuildException;
use Imiskuf\BasicApiBundle\Model\DtoInterface;
use Imiskuf\BasicApiBundle\Model\EntityInterface;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class EntityFactory
{
    /**
     * @param DtoInterface $dto
     * @param string $entityClass
     * @return EntityInterface
     * @throws EntityBuildException
     */
    public function createFromDto(DtoInterface $dto, string $entityClass): EntityInterface
    {
        $entity = new $entityClass();
        $this->setValues($dto, $entity);

        return $entity;
    }

    /**
     * @param DtoInterface $dto
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function updateFromDto(DtoInterface $dto, EntityInterface $entity): EntityInterface
    {
        $this->setValues($dto, $entity);

        return $entity;
    }

    /**
     * @param DtoInterface $dto
     * @param EntityInterface $entity
     */
    protected function setValues(DtoInterface $dto, EntityInterface &$entity): void
    {
        try {
            $propertyAccessor = new PropertyAccessor();
            foreach ($dto->toArray() as $property => $value) {
                $propertyAccessor->setValue($entity, $property, $value);
            }
        } catch (Exception $e) {
            throw new EntityBuildException('', 0, $e);
        }
    }
}