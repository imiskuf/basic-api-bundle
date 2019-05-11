<?php

namespace Imiskuf\BasicApiBundle\Model\Collection;

use Imiskuf\BasicApiBundle\Model\SerializableInterface;

class PaginatedCollection implements SerializableInterface
{
    const DEFAULT_ITEMS_PER_PAGE = 10;

    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $total;

    /**
     * @var int
     */
    private $count;

    /**
     * @var array
     */
    private $_links = [];

    /**
     * @param mixed $items
     * @param int $total
     * @param int $page
     */
    public function __construct(array $items, int $total, int $page)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
        $this->page = $page;
    }

    /**
     * @param string $ref
     * @param string $url
     */
    public function addLink(string $ref, string $url): void
    {
        $this->_links[$ref] = $url;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
