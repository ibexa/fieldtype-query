<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService;
use Pagerfanta\Adapter\AdapterInterface;

final class QueryResultsWithLocationPagerFantaAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService */
    private $queryFieldService;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location */
    private $location;

    /** @var string */
    private $fieldDefinitionIdentifier;

    public function __construct(
        QueryFieldLocationService $queryFieldService,
        Location $location,
        string $fieldDefinitionIdentifier
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->location = $location;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }

    public function getNbResults()
    {
        return $this->queryFieldService->countContentItemsForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier
        );
    }

    public function getSlice($offset, $length)
    {
        return $this->queryFieldService->loadContentItemsSliceForLocation(
            $this->location,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}
