<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Contracts\FieldTypeQuery;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * Executes queries for a query field.
 */
interface QueryFieldServiceInterface
{
    /**
     * Executes the query without pagination and returns the content items.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable;

    /**
     * Counts the total results of a query.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return int
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int;

    /**
     * Executes a paginated query and return the requested content items slice.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     * @param int $offset
     * @param int $limit
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Content[]
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function loadContentItemsSlice(Content $content, string $fieldDefinitionIdentifier, int $offset, int $limit): iterable;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Content $content
     * @param string $fieldDefinitionIdentifier
     *
     * @return int The page size, or 0 if pagination is disabled.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int;
}

class_alias(QueryFieldServiceInterface::class, 'EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface');
