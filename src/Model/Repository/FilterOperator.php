<?php

namespace Imiskuf\BasicApiBundle\Model\Repository;

use Doctrine\Common\Collections\Expr\Comparison;

interface FilterOperator
{
    const OPERATOR_MAP = [
        'eq'  => Comparison::EQ,
        'neq' => Comparison::NEQ,
        'lt'  => Comparison::LT,
        'lte' => Comparison::LTE,
        'gt'  => Comparison::GT,
        'gte' => Comparison::GTE,
        'is'  => Comparison::IS,
        'in'  => Comparison::IN,
        'nin' => Comparison::NIN,
        'like' => Comparison::CONTAINS,
        'llike' => Comparison::STARTS_WITH,
        'rlike' => Comparison::ENDS_WITH
    ];
}
