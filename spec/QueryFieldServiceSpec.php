<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\FieldTypeQuery;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Query as ApiContentQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query as ApiQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\QueryType\QueryType;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Ibexa\Core\Repository\Values;
use Ibexa\FieldTypeQuery\QueryFieldService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QueryFieldServiceSpec extends ObjectBehavior
{
    public const CONTENT_TYPE_ID = 1;
    public const CONTENT_TYPE_ID_WITHOUT_PAGINATION = 2;
    public const LOCATION_ID = 1;
    public const QUERY_TYPE_IDENTIFIER = 'query_type_identifier';
    public const FIELD_DEFINITION_IDENTIFIER = 'test';

    private ?SearchResult $searchResult = null;

    private ?array $searchHits = null;

    private int $totalCount = 0;

    public function let(
        SearchService $searchService,
        ContentTypeService $contentTypeService,
        QueryTypeRegistry $queryTypeRegistry,
        QueryType $queryType
    ): void {
        $this->searchHits = [];
        $this->searchResult = new SearchResult(['searchHits' => $this->searchHits, 'totalCount' => $this->totalCount]);

        $parameters = [
            'param1' => 'value1',
            'param2' => 'value2',
        ];

        $contentTypeWithPagination = $this->getContentType($parameters);
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID)->willReturn($contentTypeWithPagination);

        $contentTypeWithoutPagination = $this->getContentType($parameters, false, 10);
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID_WITHOUT_PAGINATION)->willReturn($contentTypeWithoutPagination);

        $queryTypeRegistry->getQueryType(self::QUERY_TYPE_IDENTIFIER)->willReturn($queryType);
        $queryType->getQuery(Argument::any())->willReturn(new ApiQuery());
        // @todo this should fail. It does not.
        $searchService->findContent(Argument::any())->willReturn($this->searchResult);
        $this->beConstructedWith($searchService, $contentTypeService, $queryTypeRegistry);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(QueryFieldService::class);
    }

    public function it_loads_items_from_a_query_field_for_a_given_content_item(): void
    {
        $this->loadContentItems($this->getContent(), self::FIELD_DEFINITION_IDENTIFIER)->shouldBe($this->searchHits);
    }

    public function it_counts_items_from_a_query_field_for_a_given_content_item(): void
    {
        $this->countContentItems($this->getContent(), self::FIELD_DEFINITION_IDENTIFIER)->shouldBe($this->totalCount);
    }

    public function it_deducts_any_offset_when_counting_results(QueryType $queryType, SearchService $searchService): void
    {
        $query = new ApiContentQuery();
        $query->offset = 5;

        $searchResult = new SearchResult(['searchHits' => [], 'totalCount' => 7]);

        $searchService->findContent($query)->willReturn($searchResult);
        $queryType->getQuery(Argument::any())->willReturn($query);

        $this->countContentItems($this->getContent(), self::FIELD_DEFINITION_IDENTIFIER)->shouldBe(2);
    }

    public function it_returns_zero_if_offset_is_bigger_than_count(QueryType $queryType, SearchService $searchService): void
    {
        $query = new ApiContentQuery();
        $query->offset = 8;

        $searchResult = new SearchResult(['searchHits' => [], 'totalCount' => 5]);

        $searchService->findContent($query)->willReturn($searchResult);
        $queryType->getQuery(Argument::any())->willReturn($query);

        $this->countContentItems($this->getContent(), self::FIELD_DEFINITION_IDENTIFIER)->shouldBe(0);
    }

    public function it_returns_0_as_pagination_configuration_if_pagination_is_disabled(): void
    {
        $this->getPaginationConfiguration(
            $this->getContent(self::CONTENT_TYPE_ID_WITHOUT_PAGINATION),
            self::FIELD_DEFINITION_IDENTIFIER
        )->shouldBe(0);
    }

    public function it_returns_the_items_per_page_number_as_pagination_configuration_if_pagination_is_enabled(): void
    {
        $this->getPaginationConfiguration(
            $this->getContent(),
            self::FIELD_DEFINITION_IDENTIFIER
        )->shouldBe(10);
    }

    /**
     * @return \Ibexa\Core\Repository\Values\Content\Content
     */
    private function getContent(int $contentTypeId = self::CONTENT_TYPE_ID): Values\Content\Content
    {
        return new Values\Content\Content([
            'versionInfo' => new Values\Content\VersionInfo([
                'contentInfo' => new ContentInfo([
                    'contentTypeId' => $contentTypeId,
                    'mainLocationId' => self::LOCATION_ID,
                    'mainLocation' => new Values\Content\Location([
                        'id' => self::LOCATION_ID,
                    ]),
                ]),
            ]),
        ]);
    }

    /**
     * @param array $parameters
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType
     */
    private function getContentType(array $parameters, bool $enablePagination = true, int $itemsPerPage = 10): ContentType
    {
        $contentType = new Values\ContentType\ContentType([
            'fieldDefinitions' => new Values\ContentType\FieldDefinitionCollection([
                new Values\ContentType\FieldDefinition([
                    'identifier' => self::FIELD_DEFINITION_IDENTIFIER,
                    'fieldTypeIdentifier' => 'ezcontentquery',
                    'fieldSettings' => [
                        'ReturnedType' => 'folder',
                        'QueryType' => self::QUERY_TYPE_IDENTIFIER,
                        'EnablePagination' => $enablePagination,
                        'ItemsPerPage' => $itemsPerPage,
                        'Parameters' => $parameters,
                    ],
                ]),
            ]),
        ]);

        return $contentType;
    }
}
