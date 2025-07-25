<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\ContentView;

use Exception;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\LocationValueView;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class QueryResultsInjector implements EventSubscriberInterface
{
    /**
     * @param \Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface&\Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService $queryFieldService
     * @param array<string, mixed> $views
     */
    public function __construct(
        private QueryFieldServiceInterface $queryFieldService,
        private array $views,
        private RequestStack $requestStack
    ) {
        if (!isset($views['item']) || !isset($views['field'])) {
            throw new \InvalidArgumentException("Both 'item' and 'field' views must be provided");
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [ViewEvents::FILTER_VIEW_PARAMETERS => 'injectQueryResults'];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function injectQueryResults(FilterViewParametersEvent $event): void
    {
        if ($event->getView()->getViewType() === $this->views['field']) {
            $builderParameters = $event->getBuilderParameters();
            if (!isset($builderParameters['queryFieldDefinitionIdentifier'])) {
                throw new InvalidArgumentException('queryFieldDefinitionIdentifier', 'missing');
            }
            $parameters = [
                'itemViewType' => $event->getBuilderParameters()['itemViewType'] ?? $this->views['item'],
                'items' => $this->buildResults($event),
                'fieldIdentifier' => $builderParameters['queryFieldDefinitionIdentifier'],
            ];
            $parameters['isPaginationEnabled'] = ($parameters['items'] instanceof Pagerfanta);
            if ($parameters['isPaginationEnabled']) {
                $parameters['pageParameter'] = sprintf('[%s_page]', $parameters['fieldIdentifier']);
            }
            $event->getParameterBag()->add($parameters);
        }
    }

    /**
     * @return iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Exception
     */
    private function buildResults(FilterViewParametersEvent $event): iterable
    {
        $view = $event->getView();
        $location = $view instanceof LocationValueView ? $view->getLocation() : null;
        $content = $view instanceof ContentValueView ? $view->getContent() : null;

        if ($location === null && $content === null) {
            throw new Exception('No content nor location to get query results for');
        }
        $viewParameters = $event->getBuilderParameters();
        $fieldDefinitionIdentifier = $viewParameters['queryFieldDefinitionIdentifier'];

        $paginationLimit = $this->queryFieldService->getPaginationConfiguration(
            $content ?? $location->getContent(),
            $fieldDefinitionIdentifier
        );

        $enablePagination = ($viewParameters['enablePagination'] === true);
        $disablePagination = ($viewParameters['disablePagination'] === true);

        if ($enablePagination === true && $disablePagination === true) {
            // @todo custom exception
            throw new \InvalidArgumentException("the 'enablePagination' and 'disablePagination' parameters can not both be true");
        }

        if (isset($viewParameters['itemsPerPage']) && is_numeric($viewParameters['itemsPerPage'])) {
            // @todo custom exception
            if ($viewParameters['itemsPerPage'] <= 0) {
                throw new \InvalidArgumentException('itemsPerPage must be a positive integer');
            }
            $paginationLimit = $viewParameters['itemsPerPage'];
        }

        if (($enablePagination === true) && (!$paginationLimit || $paginationLimit <= 0)) {
            throw new \InvalidArgumentException("The 'itemsPerPage' parameter must be given with a positive integer value if 'enablePagination' is set");
        }

        if ($paginationLimit !== 0 && $disablePagination !== true) {
            $request = $this->requestStack->getMainRequest();

            $queryParameters = $view->hasParameter('query') ? $view->getParameter('query') : [];

            $limit = $queryParameters['limit'] ?? $paginationLimit;
            $pageParam = sprintf('%s_page', $fieldDefinitionIdentifier);
            $page = isset($request) ? $request->get($pageParam, 1) : 1;

            if ($location !== null) {
                $pager = new Pagerfanta(
                    new QueryResultsWithLocationPagerFantaAdapter(
                        $this->queryFieldService,
                        $location,
                        $fieldDefinitionIdentifier
                    )
                );
            } else {
                $pager = new Pagerfanta(
                    new QueryResultsPagerFantaAdapter(
                        $this->queryFieldService,
                        $content,
                        $fieldDefinitionIdentifier
                    )
                );
            }

            $pager->setMaxPerPage($limit);
            $pager->setCurrentPage($page);

            return $pager;
        } else {
            if ($location !== null) {
                return $this->queryFieldService->loadContentItemsForLocation(
                    $location,
                    $fieldDefinitionIdentifier
                );
            } elseif ($content !== null) {
                return $this->queryFieldService->loadContentItems(
                    $content,
                    $fieldDefinitionIdentifier
                );
            } else {
                throw new Exception('No content nor location to get query results for');
            }
        }
    }
}
