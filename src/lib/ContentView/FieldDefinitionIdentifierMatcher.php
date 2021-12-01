<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
     * {@inheritdoc}
     */
    public function matchLocation(Location $location)
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($location->getContentInfo()->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    /**
     * {@inheritdoc}
     */
    public function matchContentInfo(ContentInfo $contentInfo)
    {
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($contentInfo->contentTypeId);

        return $this->hasFieldDefinition($contentType);
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType $contentType
     *
     * @return bool
     */
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
     * @param \Ibexa\Core\MVC\Symfony\View\View $view
     *
     * @return bool
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function match(View $view)
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($view->getContent()->contentInfo->contentTypeId);

        if (!$this->hasFieldDefinition($contentType)) {
            return false;
        }

        if (!$view->hasParameter('fieldIdentifier')) {
            return false;
        }

        return in_array($view->getParameter('fieldIdentifier'), $this->getValues(), true);
    }
}

class_alias(FieldDefinitionIdentifierMatcher::class, 'EzSystems\EzPlatformQueryFieldType\eZ\ContentView\FieldDefinitionIdentifierMatcher');
