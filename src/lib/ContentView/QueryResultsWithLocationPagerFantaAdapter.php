<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
 */
final readonly class QueryResultsWithLocationPagerFantaAdapter implements AdapterInterface
{
    public function __construct(
        private QueryFieldLocationService $queryFieldService,
        private Location $location,
        private string $fieldDefinitionIdentifier
    ) {
    }

    public function getNbResults(): int
    {
        return max($this->queryFieldService->countContentItemsForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier
        ), 0);
    }

    public function getSlice($offset, $length): iterable
    {
        return $this->queryFieldService->loadContentItemsSliceForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}
