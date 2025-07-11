<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\FieldTypeQuery\Controller;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Ibexa\FieldTypeQuery\QueryFieldService;
use function Ibexa\PolyfillPhp82\iterator_to_array;
use Ibexa\Rest\Server\Values as RestValues;
use Ibexa\Rest\Server\Values\RestContent;
use Symfony\Component\HttpFoundation\Request;

final readonly class QueryFieldRestController
{
    public function __construct(
        private QueryFieldService $queryFieldService,
        private ContentService $contentService,
        private ContentTypeService $contentTypeService,
        private LocationService $locationService,
        private UriParserInterface $uriParser,
        private ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
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
            $content = $this->contentService->loadContent($contentId, null, $versionNumber);
            if ($limit === -1) {
                $items = $this->queryFieldService->loadContentItems($content, $fieldDefinitionIdentifier);
            } else {
                $items = $this->queryFieldService->loadContentItemsSlice($content, $fieldDefinitionIdentifier, $offset, $limit);
            }
        }

        return new RestValues\ContentList(
            array_map(
                function (Content $content): RestContent {
                    $contentInfo = $content->getContentInfo();
                    if ($contentInfo->getMainLocationId() === null) {
                        throw new NotFoundException(
                            sprintf('Content with ID "%s" has no main location.', $contentInfo->getId())
                        );
                    }

                    return new RestContent(
                        $content->getContentInfo(),
                        $this->locationService->loadLocation($contentInfo->getMainLocationId()),
                        $content,
                        $this->getContentType($contentInfo),
                        iterator_to_array($this->relationListFacade->getRelations($content->getVersionInfo()))
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    private function loadLocationByPath(Request $request): Location
    {
        $locationHrefParts = explode(
            '/',
            $this->uriParser->getAttributeFromUri(
                $request->query->get('location') ?? '',
                'locationPath'
            )
        );

        $locationId = array_pop($locationHrefParts);

        return $this->locationService->loadLocation((int)$locationId);
    }
}
