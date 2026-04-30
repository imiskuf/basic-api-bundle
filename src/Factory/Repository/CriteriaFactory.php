<?php

namespace Imiskuf\BasicApiBundle\Factory\Repository;

use Imiskuf\BasicApiBundle\Enum\FilterMode;
use Imiskuf\BasicApiBundle\Exception\Repository\FilterArgumentException;
use Imiskuf\BasicApiBundle\Model\Repository\FilterOperator;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;

class CriteriaFactory
{
    /**
     * @var array
     */
    private $allowedProperties;

    /**
     * @var array
     */
    private $propertyMap;

    /**
     * @param array $allowedProperties
     * @param array $propertyMap
     */
    public function __construct(array $allowedProperties, array $propertyMap = [])
    {
        $this->allowedProperties = $allowedProperties;
        $this->propertyMap = $propertyMap;
    }

    /**
     * Flat filter mode: all conditions combined with a single AND/OR mode.
     *
     * Syntax: filter[<property>][<operator>]=<value>&mode=and|or
     */
    public function createFilterCriteria(array $filterData, string $mode): Criteria
    {
        $mode = strtolower($mode);
        $this->validateMode($mode);

        $conditions = $this->buildConditions($filterData);
        if (empty($conditions)) {
            return new Criteria();
        }

        $compositeType = $mode === FilterMode::AND
            ? CompositeExpression::TYPE_AND
            : CompositeExpression::TYPE_OR;

        return new Criteria(new CompositeExpression($compositeType, $conditions));
    }

    /**
     * Grouped filter mode with recursive nesting support.
     *
     * Each node can contain 'conditions' (leaf comparisons), 'groups' (sub-nodes), or both.
     * All expressions within a node are combined using the node's 'mode' (defaults to 'and').
     *
     * Syntax:
     *   filterGroups[mode]=or
     *   filterGroups[groups][0][mode]=and
     *   filterGroups[groups][0][conditions][<property>][<operator>]=<value>
     *   filterGroups[groups][1][mode]=and
     *   filterGroups[groups][1][groups][0][conditions][<property>][<operator>]=<value>
     *   filterGroups[groups][1][groups][1][conditions][<property>][<operator>]=<value>
     *
     * @param array $node  Root node with optional 'mode', 'conditions', and 'groups'
     */
    public function createGroupedFilterCriteria(array $node): Criteria
    {
        $expression = $this->buildGroup($node);
        if ($expression === null) {
            return new Criteria();
        }

        return new Criteria($expression);
    }

    private function buildGroup(array $node): ?Expression
    {
        $mode = strtolower($node['mode'] ?? FilterMode::AND);
        $this->validateMode($mode);

        $expressions = [];

        if (isset($node['conditions']) && is_array($node['conditions'])) {
            $expressions = array_merge($expressions, $this->buildConditions($node['conditions']));
        }

        if (isset($node['groups']) && is_array($node['groups'])) {
            foreach ($node['groups'] as $subGroup) {
                $subExpression = $this->buildGroup($subGroup);
                if ($subExpression !== null) {
                    $expressions[] = $subExpression;
                }
            }
        }

        if (empty($expressions)) {
            return null;
        }

        $compositeType = $mode === FilterMode::AND
            ? CompositeExpression::TYPE_AND
            : CompositeExpression::TYPE_OR;

        return new CompositeExpression($compositeType, $expressions);
    }

    /**
     * @return Expression[]
     */
    private function buildConditions(array $filterData): array
    {
        $conditions = [];
        foreach ($filterData as $propertyName => $expression) {
            if (!in_array($propertyName, $this->allowedProperties)) {
                $filters = "'" . implode("', '", $this->allowedProperties) . "'";

                throw new FilterArgumentException(
                    "Filter for property '{$propertyName}' is not allowed! Allowed: {$filters}."
                );
            }

            foreach ($expression as $operator => $value) {
                $operator = strtolower($operator);
                if ($operator === FilterOperator::NULL_OPERATOR) {
                    $conditions[] = new Comparison(
                        $this->propertyMap[$propertyName] ?? $propertyName,
                        (bool) $value ? Comparison::EQ : Comparison::NEQ,
                        null
                    );

                    continue;
                }

                $mappedOperator = $this->getMappedOperator($operator);

                $conditions[] = new Comparison(
                    $this->propertyMap[$propertyName] ?? $propertyName,
                    $mappedOperator,
                    $this->getMappedValue($mappedOperator, $value)
                );
            }
        }

        return $conditions;
    }

    private function validateMode(string $mode): void
    {
        if (!in_array($mode, [FilterMode::AND, FilterMode::OR])) {
            throw new FilterArgumentException("Invalid filter mode '$mode'! Allowed modes: 'and', 'or'.");
        }
    }

    /**
     * @param array $orderData
     * @return Criteria
     */
    public function createOrderCriteria(array $orderData): Criteria
    {
        $orderParameters = [];
        foreach ($orderData as $propertyName => $order) {
            if (!in_array($propertyName, $this->allowedProperties)) {
                $filters = "'" . implode("','", $this->allowedProperties) . "'";

                throw new FilterArgumentException(
                    "Order by property '{$propertyName}' is not allowed! Allowed: {$filters}."
                );
            }

            $order = strtoupper($order);
            if (!in_array($order, [Criteria::ASC, Criteria::DESC])) {
                $orders = "'" . Criteria::ASC . "', '" . Criteria::DESC. "'";

                throw new FilterArgumentException(
                    "Order type '{$order}' is not allowed! Allowed: {$orders}."
                );
            }

            $orderParameters[$this->propertyMap[$propertyName] ?? $propertyName] = $order;
        }

        return new Criteria(null, $orderParameters);
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