<?php

namespace BasicApi\Factory\Repository;

use BasicApi\Exception\Repository\FilterArgumentException;
use BasicApi\Model\Repository\FilterOperator;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

class CriteriaFactory
{
    /**
     * @var array
     */
    private $allowedProperties;

    /**
     * @param array $allowedProperties
     */
    public function __construct(array $allowedProperties)
    {
        $this->allowedProperties = $allowedProperties;
    }

    /**
     * @param array $filterData
     * @return Criteria
     */
    public function createFilterCriteria(array $filterData): Criteria
    {
        $criteria = new Criteria();
        foreach ($filterData as $propertyName => $expression) {
            if (!in_array($propertyName, $this->allowedProperties)) {
                $filters = "'" . implode("','", $this->allowedProperties) . "'";

                throw new FilterArgumentException(
                    "Filter for property '{$propertyName}' is not allowed! Allowed: {$filters}."
                );
            }

            foreach ($expression as $operator => $value) {
                $mappedOperator = $this->getMappedOperator($operator);
                $criteria->andWhere(
                    new Comparison($propertyName, $mappedOperator, $this->getMappedValue($mappedOperator, $value))
                );
            }
        }

        return $criteria;
    }

    /**
     * @param array $orderData
     * @return Criteria
     */
    public function createOrderCriteria(array $orderData): Criteria
    {
        foreach ($orderData as $propertyName => $order) {
            if (!in_array($propertyName, $this->allowedProperties)) {
                $filters = "'" . implode("','", $this->allowedProperties) . "'";

                throw new FilterArgumentException(
                    "Order by property '{$propertyName}' is not allowed! Allowed: {$filters}."
                );
            }

            $order = strtoupper($order);
            if (!in_array($order, [Criteria::ASC, Criteria::DESC])) {
                $orders = "'" . Criteria::ASC . "','" . Criteria::DESC. "'";

                throw new FilterArgumentException(
                    "Order type '{$order}' is not allowed! Allowed: {$orders}."
                );
            }
        }

        return new Criteria(null, $orderData);
    }

    /**
     * @param string $originalOperator
     * @return string
     */
    private function getMappedOperator(string $originalOperator): string
    {
        $map = FilterOperator::OPERATOR_MAP;
        $operator = strtolower($originalOperator);
        if (array_key_exists($operator, $map)) {
            return $map[$operator];
        }

        $operators = implode(', ', array_keys($map));
        throw new FilterArgumentException("Invalid comparison operator {$operator}! Allowed operators: {$operators}.");
    }

    /**
     * @param string $mappedOperator
     * @param string $originalValue
     * @return mixed
     */
    private function getMappedValue(string $mappedOperator, string $originalValue)
    {
        switch ($mappedOperator) {
            case Comparison::IN:
            case Comparison::NIN:
                return explode(',', $originalValue);
        }

        return $originalValue;
    }
}
