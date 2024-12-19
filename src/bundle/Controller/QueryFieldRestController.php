<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\FieldTypeQuery\Controller;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\FieldTypeQuery\QueryFieldService;
use function Ibexa\PolyfillPhp82\iterator_to_array;
use Ibexa\Rest\RequestParser;
use Ibexa\Rest\Server\Values as RestValues;
use Symfony\Component\HttpFoundation\Request;

final class QueryFieldRestController
{
    /** @var \Ibexa\FieldTypeQuery\QueryFieldService */
    private $queryFieldService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentService */
    private $contentService;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Ibexa\Contracts\Core\Repository\LocationService */
    private $locationService;

    /** @var \Ibexa\Rest\RequestParser */
    private $requestParser;

    public function __construct(
        QueryFieldService $queryFieldService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        LocationService $locationService,
        RequestParser $requestParser
    ) {
        $this->queryFieldService = $queryFieldService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->locationService = $locationService;
        $this->requestParser = $requestParser;
    }

    public function getResults(
        Request $request,
        int $contentId,
        int $versionNumber,
        string $fieldDefinitionIdentifier
    ): RestValues\ContentList {
        $offset = (int)$request->query->get('offset', '0');
        $limit = (int)$request->query->get('limit', '-1');

        if ($request->query->has('location')) {
            $location = $this->loadLocationByPath($request);
            $content = $location->getContent();
            if ($content->id !== $contentId) {
                $message = sprintf(
                    'Content with contentId "%s" does not match content found using locationId "%s"',
                    $contentId,
                    $content->id
                );
                throw new NotFoundException($message);
            }
            if ($limit === -1) {
                $items = $this->queryFieldService->loadContentItemsForLocation($location, $fieldDefinitionIdentifier);
            } else {
                $items = $this->queryFieldService->loadContentItemsSliceForLocation($location, $fieldDefinitionIdentifier, $offset, $limit);
            }
        } else {
            $location = null;
            $content = $this->contentService->loadContent($contentId, null, $versionNumber);
            if ($limit === -1 || !method_exists($this->queryFieldService, 'loadContentItemsSlice')) {
                $items = $this->queryFieldService->loadContentItems($content, $fieldDefinitionIdentifier);
            } else {
                $items = $this->queryFieldService->loadContentItemsSlice($content, $fieldDefinitionIdentifier, $offset, $limit);
            }
        }

        return new RestValues\ContentList(
            array_map(
                function (Content $content) {
                    return new RestValues\RestContent(
                        $content->contentInfo,
                        $this->locationService->loadLocation($content->contentInfo->mainLocationId),
                        $content,
                        $this->getContentType($content->contentInfo),
                        iterator_to_array($this->contentService->loadRelations($content->getVersionInfo()))
                    );
                },
                iterator_to_array($items)
            ),
            $this->queryFieldService->countContentItems($content, $fieldDefinitionIdentifier)
        );
    }

    private function getContentType(ContentInfo $contentInfo): ContentType
    {
        static $contentTypes = [];

        if (!isset($contentTypes[$contentInfo->contentTypeId])) {
            $contentTypes[$contentInfo->contentTypeId] = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        }

        return $contentTypes[$contentInfo->contentTypeId];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function loadLocationByPath(Request $request): Location
    {
        $locationHrefParts = explode('/', $this->requestParser->parseHref($request->query->get('location'), 'locationPath'));
        $locationId = array_pop($locationHrefParts);

        return $this->locationService->loadLocation((int)$locationId);
    }
}

class_alias(QueryFieldRestController::class, 'EzSystems\EzPlatformQueryFieldType\Controller\QueryFieldRestController');
