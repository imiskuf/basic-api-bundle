<?php

namespace BasicApi\Factory\Entity;

use BasicApi\Exception\Entity\EntityBuildException;
use BasicApi\Model\DtoInterface;
use Exception;
use Mamatata\Model\Common\EntityInterface;
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