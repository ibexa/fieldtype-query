<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
 */
final class QueryResultsWithLocationPagerFantaAdapter implements AdapterInterface
{
    private QueryFieldLocationService $queryFieldService;

    private Location $location;

    private string $fieldDefinitionIdentifier;

    public function __construct(
        QueryFieldLocationService $queryFieldService,
        Location $location,
        string $fieldDefinitionIdentifier
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->location = $location;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
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
