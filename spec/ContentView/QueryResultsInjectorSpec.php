<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\MVC\Symfony\View\Event\FilterViewParametersEvent;
use Ibexa\Core\MVC\Symfony\View\ViewEvents;
use Ibexa\Core\Repository\Values\Content\Content;
use Pagerfanta\Pagerfanta;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class QueryResultsInjectorSpec extends ObjectBehavior
{
    public const string FIELD_VIEW = 'content_query_field';
    public const string OTHER_VIEW = 'anything_else';
    public const string ITEM_VIEW = 'line';
    public const array VIEWS = ['field' => self::FIELD_VIEW, 'item' => self::ITEM_VIEW];
    public const string FIELD_DEFINITION_IDENTIFIER = 'query_field';

    private ContentView $view;

    private FilterViewParametersEvent $event;

    public function __construct()
    {
        $this->view = new ContentView(
            null,
            [],
            self::FIELD_VIEW,
        );
        $this->view->setContent($this->createContentItem());
        $this->event = new FilterViewParametersEvent(
            $this->view,
            [
                'queryFieldDefinitionIdentifier' => self::FIELD_DEFINITION_IDENTIFIER,
                'enablePagination' => false,
                'disablePagination' => false,
            ]
        );
    }

    public function let(
        QueryFieldServiceInterface $queryFieldService,
        FilterViewParametersEvent $event,
        RequestStack $requestStack
    ): void {
        $this->beConstructedWith($queryFieldService, self::VIEWS, $requestStack);
        $event->getView()->willReturn($this->view);
        $event->getBuilderParameters()->willReturn(
            [
                'queryFieldDefinitionIdentifier' => self::FIELD_DEFINITION_IDENTIFIER,
                'enablePagination' => false,
                'disablePagination' => false,
            ]
        );
    }

    public function it_throws_an_InvalidArgumentException_if_no_item_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    ): void {
        $this->beConstructedWith($queryFieldService, ['field' => self::FIELD_VIEW], $requestStack);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_InvalidArgumentException_if_no_field_view_is_provided(
        QueryFieldServiceInterface $queryFieldService,
        RequestStack $requestStack
    ): void {
        $this->beConstructedWith($queryFieldService, ['item' => 'field'], $requestStack);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_is_an_event_subscriber(): void
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_subscribes_to_the_FILTER_VIEW_PARAMETERS_View_Event(): void
    {
        $this->getSubscribedEvents()->shouldSubscribeTo(ViewEvents::FILTER_VIEW_PARAMETERS);
    }

    public function it_does_nothing_for_non_field_views(QueryFieldServiceInterface $queryFieldService): void
    {
        $this->event->getView()->setViewType(self::OTHER_VIEW);
        $this->injectQueryResults($this->event);
        $queryFieldService->getPaginationConfiguration(Argument::any())->shouldNotHaveBeenCalled();
    }

    public function it_adds_the_query_results_for_the_field_view_without_pagination(
        QueryFieldServiceInterface $queryFieldService
    ): void {
        $content = $this->createContentItem();

        $queryFieldService
            ->getPaginationConfiguration($content, self::FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(0);

        $queryFieldService->loadContentItems(
            $content,
            self::FIELD_DEFINITION_IDENTIFIER
        )->willReturn($this->getResults());

        $this->injectQueryResults($this->event);

        $parameters = $this->event->getParameterBag();
        Assert::true($parameters->has('itemViewType'));
        Assert::eq($parameters->get('itemViewType'), self::ITEM_VIEW);
        Assert::true($parameters->has('isPaginationEnabled'));
        Assert::eq($parameters->get('isPaginationEnabled'), false);
        Assert::true($parameters->has('items'));
        Assert::eq($parameters->get('items'), $this->getResults());
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function it_adds_the_query_results_for_the_field_view_with_pagination(
        FilterViewParametersEvent $event,
        QueryFieldServiceInterface $queryFieldService
    ): void {
        $content = $this->createContentItem();

        $queryFieldService
            ->getPaginationConfiguration($content, self::FIELD_DEFINITION_IDENTIFIER)
            ->willReturn(5);

        $queryFieldService->loadContentItems(
            $content,
            self::FIELD_DEFINITION_IDENTIFIER
        )->willReturn($this->getResults());

        $this->injectQueryResults($this->event);

        $parameters = $this->event->getParameterBag();
        Assert::true($parameters->has('itemViewType'));
        Assert::eq($parameters->get('itemViewType'), self::ITEM_VIEW);
        Assert::true($parameters->has('isPaginationEnabled'));
        Assert::eq($parameters->get('isPaginationEnabled'), true);
        Assert::true($parameters->has('pageParameter'));
        Assert::eq($parameters->get('pageParameter'), '[' . self::FIELD_DEFINITION_IDENTIFIER . '_page]');
        Assert::true($parameters->has('items'));
        Assert::isInstanceOf($parameters->get('items'), Pagerfanta::class);
    }

    /**
     * @return array<string, callable>
     */
    public function getMatchers(): array
    {
        return [
            'subscribeTo' => static function ($return, $event): bool {
                return is_array($return) && isset($return[$event]);
            },
        ];
    }

    private function createContentItem(): Content
    {
        return new Content();
    }

    /**
     * @return \Ibexa\Core\Repository\Values\Content\Content[]
     */
    private function getResults(): array
    {
        return [
            new Content(),
            new Content(),
        ];
    }
}
