<?php

namespace BasicApi\Factory\Collection;

use BasicApi\Model\Collection\PaginatedCollection;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class PaginatedCollectionFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Pagerfanta $pagerfanta
     * @param Request $request
     * @return PaginatedCollection
     */
    public function createCollection(Pagerfanta $pagerfanta, Request $request): PaginatedCollection
    {
        $page = $request->query->get('page', 1);
        $maxPerPage = $request->query->get('count', PaginatedCollection::DEFAULT_ITEMS_PER_PAGE);

        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        $items = iterator_to_array($pagerfanta->getCurrentPageResults());

        $paginatedCollection = new PaginatedCollection($items, $pagerfanta->getNbResults(), $page);
        $this->addLinks($paginatedCollection, $pagerfanta, $page, $request);

        return $paginatedCollection;
    }

    /**
     * @param PaginatedCollection $paginatedCollection
     * @param Pagerfanta $pagerfanta
     * @param int $page
     * @param Request $request
     */
    private function addLinks(
        PaginatedCollection $paginatedCollection,
        Pagerfanta $pagerfanta,
        int $page,
        Request $request
    ): void
    {
        $route = $request->attributes->get('_route');
        if (null === $route) {
            return;
        }

        $routeParameters = array_merge(
            $request->attributes->get('_route_params', []),
            $request->query->all()
        );
        $createLinkUrl = function (int $targetPage) use ($route, $routeParameters) {
            return urldecode(
                $this->router->generate(
                    $route,
                    array_merge($routeParameters, ['page' => $targetPage]),
                    Router::ABSOLUTE_URL
                )
            );
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }

        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }
    }
}