<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Pagerfanta\Adapter\AdapterInterface;

final class QueryResultsPagerFantaAdapter implements AdapterInterface
{
    /** @var \Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface */
    private $queryFieldService;

    /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content */
    private $content;

    /** @var string */
    private $fieldDefinitionIdentifier;

    public function __construct(
        QueryFieldServiceInterface $queryFieldService,
        Content $content,
        string $fieldDefinitionIdentifier
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->content = $content;
        $this->fieldDefinitionIdentifier = $fieldDefinitionIdentifier;
    }

    public function getNbResults()
    {
        return $this->queryFieldService->countContentItems(
            $this->content,
            $this->fieldDefinitionIdentifier
        );
    }

    public function getSlice($offset, $length)
    {
        return $this->queryFieldService->loadContentItemsSlice(
            $this->content,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}
