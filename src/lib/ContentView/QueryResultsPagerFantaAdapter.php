<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements \Pagerfanta\Adapter\AdapterInterface<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
 */
final readonly class QueryResultsPagerFantaAdapter implements AdapterInterface
{
    public function __construct(
        private QueryFieldServiceInterface $queryFieldService,
        private Content $content,
        private string $fieldDefinitionIdentifier
    ) {
    }

    public function getNbResults(): int
    {
        return max($this->queryFieldService->countContentItems(
            $this->content,
            $this->fieldDefinitionIdentifier
        ), 0);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function getSlice($offset, $length): iterable
    {
        return $this->queryFieldService->loadContentItemsSlice(
            $this->content,
            $this->fieldDefinitionIdentifier,
            $offset,
            $length
        );
    }
}
