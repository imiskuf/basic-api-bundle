<?php

namespace Imiskuf\BasicApiBundle\Model\Collection;

use Imiskuf\BasicApiBundle\Model\SerializableInterface;

class Collection implements SerializableInterface
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @param mixed $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
