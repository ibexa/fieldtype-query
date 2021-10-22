<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\FieldTypeQuery;

/**
 * Pagination related methods for v1.0.
 *
 * @deprecated since 1.0, will be part of the regular QueryFieldService interface in 2.0.
 */
interface QueryFieldPaginationService
{
}

class_alias(QueryFieldPaginationService::class, 'EzSystems\EzPlatformQueryFieldType\API\QueryFieldPaginationService');
