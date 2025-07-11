<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Executes a query and returns the results.
 */
final readonly class QueryFieldService implements QueryFieldServiceInterface, QueryFieldLocationService
{
    public function __construct(
        private SearchService $searchService,
        private ContentTypeService $contentTypeService,
        private QueryTypeRegistry $queryTypeRegistry
    ) {
    }

    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        $mainLocation = $content->getContentInfo()->getMainLocation();
        if ($mainLocation === null) {
            return [];
        }
        $query = $this->prepareQuery($content, $mainLocation, $fieldDefinitionIdentifier);

        return $this->executeQueryAndMapResult($query);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): iterable
    {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);

        return $this->executeQueryAndMapResult($query);
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        $mainLocation = $content->getContentInfo()->getMainLocation();
        if ($mainLocation === null) {
            return 0;
        }
        $query = $this->prepareQuery($content, $mainLocation, $fieldDefinitionIdentifier);
        $query->limit = 0;

        $count = $this->searchService->findContent($query)->totalCount - $query->offset;

        return $count < 0 ? 0 : $count;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function countContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): int
    {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);
        $query->limit = 0;

        $count = $this->searchService->findContent($query)->totalCount - $query->offset;

        return $count < 0 ? 0 : $count;
    }

    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable
    {
        $mainLocation = $content->getContentInfo()->getMainLocation();
        if ($mainLocation === null) {
            return [];
        }
        $query = $this->prepareQuery($content, $mainLocation, $fieldDefinitionIdentifier);
        $query->offset += $offset;
        $query->limit = $limit;

        return $this->executeQueryAndMapResult($query);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadContentItemsSliceForLocation(
        Location $location,
        string $fieldDefinitionIdentifier,
        int $offset,
        int $limit
    ): iterable {
        $query = $this->prepareQuery($location->getContent(), $location, $fieldDefinitionIdentifier);
        $query->offset += $offset;
        $query->limit = $limit;

        return $this->executeQueryAndMapResult($query);
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        if ($fieldDefinition->getFieldSettings()['EnablePagination'] === false) {
            return 0;
        }

        return $fieldDefinition->getFieldSettings()['ItemsPerPage'];
    }

    /**
     * @param array<string, string> $expressions parameters that may include expressions to be resolved
     * @param array<string, mixed> $variables
     *
     * @return array<string, mixed>
     */
    private function resolveParameters(array $expressions, array $variables): array
    {
        foreach ($expressions as $key => $expression) {
            if (is_array($expression)) {
                $expressions[$key] = $this->resolveParameters($expression, $variables);
            } elseif ($this->isExpression($expression)) {
                $expressions[$key] = $this->resolveExpression($expression, $variables);
            } else {
                $expressions[$key] = $expression;
            }
        }

        return $expressions;
    }

    private function isExpression(mixed $expression): bool
    {
        return is_string($expression) && str_starts_with($expression, '@=');
    }

    /**
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException if $expression is not an expression.
     */
    private function resolveExpression(string $expression, array $variables): mixed
    {
        if (!$this->isExpression($expression)) {
            throw new InvalidArgumentException('expression', 'is not an expression');
        }

        return (new ExpressionLanguage())->evaluate(substr($expression, 2), $variables);
    }

    /**
     * @param array<string, mixed> $extraParameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function prepareQuery(
        Content $content,
        Location $location,
        string $fieldDefinitionIdentifier,
        array $extraParameters = []
    ): Query {
        $fieldDefinition = $this->loadFieldDefinition($content, $fieldDefinitionIdentifier);

        $queryType = $this->queryTypeRegistry->getQueryType($fieldDefinition->getFieldSettings()['QueryType']);
        $contentInfo = $content->getContentInfo();

        $parameters = $this->resolveParameters(
            $fieldDefinition->getFieldSettings()['Parameters'],
            array_merge(
                $extraParameters,
                [
                    'content' => $content,
                    'contentInfo' => $contentInfo,
                    'location' => $location,
                    'mainLocation' => $location->id === $contentInfo->mainLocationId ? $location : $contentInfo->getMainLocation(),
                    'returnedType' => $fieldDefinition->getFieldSettings()['ReturnedType'],
                ]
            )
        );

        return $queryType->getQuery($parameters);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function loadFieldDefinition(Content $content, string $fieldDefinitionIdentifier): FieldDefinition
    {
        $contentType = $this->contentTypeService->loadContentType(
            $content->getContentInfo()->contentTypeId
        );

        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);

        if ($fieldDefinition === null) {
            throw new NotFoundException(
                'Query field definition',
                $contentType->getIdentifier() . '/' . $fieldDefinitionIdentifier
            );
        }

        return $fieldDefinition;
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    private function executeQueryAndMapResult(Query $query): array
    {
        return array_map(
            static function (SearchHit $searchHit): Content {
                return $searchHit->valueObject;
            },
            $this->searchService->findContent($query)->searchHits
        );
    }
}
