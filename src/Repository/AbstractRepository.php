<?php

namespace Imiskuf\BasicApiBundle\Repository;

use Imiskuf\BasicApiBundle\Factory\Repository\CriteriaFactory;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractRepository extends EntityRepository
{
    /**
     * Syntax: filter[<propertyName>][<operator>]=<value>
     * e.g:
     * /endpoint?filter[enabled][eq]=1
     * /endpoint?filter[category][gte]=10&filter[category][lt]=20
     *
     * @param ParameterBag $parameters
     * @param array $excludedProperties
     * @return QueryBuilder
     * @throws \Imiskuf\BasicApiBundle\Exception\Repository\FilterArgumentException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getSearchQueryBuilder(ParameterBag $parameters, array $excludedProperties = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('entity');

        $filterFactory = new CriteriaFactory($this->getAllowedProperties($excludedProperties));
        if ($parameters->has('filter')) {
            $qb->addCriteria($filterFactory->createFilterCriteria($parameters->get('filter')));
        }

        if ($parameters->has('order')) {
            $qb->addCriteria($filterFactory->createOrderCriteria($parameters->get('order')));
        }

        return $qb;
    }

    /**
     * @param array $excludedProperties
     * @return array
     */
    private function getAllowedProperties(array $excludedProperties): array
    {
        return array_diff(
            array_keys($this->getClassMetadata()->getReflectionProperties()),
            $excludedProperties
        );
    }
}
