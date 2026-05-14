<?php

namespace Imiskuf\BasicApiBundle\Model\Collection;

use JMS\Serializer\Annotation as Serializer;

class PaginatedCollection extends Collection
{
    const DEFAULT_ITEMS_PER_PAGE = 10;

    #[Serializer\Groups(["collection"])]
    private $page;

    #[Serializer\Groups(["collection"])]
    private $total;

    #[Serializer\Groups(["collection"])]
    private $count;

    #[Serializer\Groups(["collection"])]
    private $_links = [];

    #[Serializer\Groups(["collection"])]
    private $extra = [];

    /**
     * @param mixed $items
     * @param int $total
     * @param int $page
     */
    public function __construct(array $items, int $total, int $page)
    {
        $this->total = $total;
        $this->count = count($items);
        $this->page = $page;

        parent::__construct($items);
    }

    public function addExtra(string $key, $value): void
    {
        $this->extra[$key] = $value;
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
