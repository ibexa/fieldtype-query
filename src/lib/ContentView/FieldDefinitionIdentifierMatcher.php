<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use Ibexa\Core\MVC\Symfony\View\ContentValueView;
use Ibexa\Core\MVC\Symfony\View\View;

final class FieldDefinitionIdentifierMatcher extends MultipleValued
{
    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function matchLocation(Location $location): bool
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($location->getContentInfo()->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function matchContentInfo(ContentInfo $contentInfo): bool
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($contentInfo->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    private function hasFieldDefinition(ContentType $contentType): bool
    {
        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if (in_array($fieldDefinition->identifier, $this->getValues(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function match(View $view): bool
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }

        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType(
                $view->getContent()->getContentInfo()->getContentType()->id
            );

        if (!$this->hasFieldDefinition($contentType)) {
            return false;
        }

        if (!$view->hasParameter('fieldIdentifier')) {
            return false;
        }

        return in_array(
            $view->getParameter('fieldIdentifier'),
            $this->getValues(),
            true
        );
    }
}
