<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\FieldTypeQuery;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

/**
 * Executes queries for a query field.
 */
interface QueryFieldServiceInterface
{
    /**
     * Executes the query without pagination and returns the content items.
     *
     * @return Content[]
     *
     * @throws InvalidArgumentException
     */
    public function loadContentItems(
        Content $content,
        string $fieldDefinitionIdentifier
    ): iterable;

    /**
     * Counts the total results of a query.
     *
     * @throws InvalidArgumentException
     */
    public function countContentItems(
        Content $content,
        string $fieldDefinitionIdentifier
    ): int;

    /**
     * Executes a paginated query and return the requested content items slice.
     *
     * @return Content[]
     *
     * @throws InvalidArgumentException
     */
    public function loadContentItemsSlice(
        Content $content,
        string $fieldDefinitionIdentifier,
        int $offset,
        int $limit
    ): iterable;

    /**
     * @return int The page size, or 0 if pagination is disabled.
     *
     * @throws InvalidArgumentException
     */
    public function getPaginationConfiguration(
        Content $content,
        string $fieldDefinitionIdentifier
    ): int;
}

class_alias(QueryFieldServiceInterface::class, 'EzSystems\EzPlatformQueryFieldType\API\QueryFieldServiceInterface');
