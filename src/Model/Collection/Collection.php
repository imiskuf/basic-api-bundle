<?php

namespace Imiskuf\BasicApiBundle\Model\Collection;

use Imiskuf\BasicApiBundle\Model\SerializableInterface;
use JMS\Serializer\Annotation as Serializer;

class Collection implements SerializableInterface
{
    /**
     * @var array
     * @Serializer\Groups({"collection"})
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
