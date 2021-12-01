<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\Ibexa\FieldTypeQuery\ContentView;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\MVC\Symfony\Matcher\ViewMatcherInterface;
use Ibexa\Core\MVC\Symfony\View\ContentView;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use Ibexa\FieldTypeQuery\ContentView\FieldDefinitionIdentifierMatcher;
use PhpSpec\ObjectBehavior;

class FieldDefinitionIdentifierMatcherSpec extends ObjectBehavior
{
    private const CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION = 2;
    private const CONTENT_TYPE_IDENTIFIER_WITHOUT_FIELD_DEFINITION = 'type_matching_without_field_def';

    private const CONTENT_TYPE_ID_WITH_FIELD_DEFINITION = 3;
    private const CONTENT_TYPE_IDENTIFIER_WITH_FIELD_DEFINITION = 'type_matching_with_field_def';

    const FIELD_DEFINITION_IDENTIFIER = 'field_definition';

    function it_is_initializable()
    {
        $this->shouldHaveType(FieldDefinitionIdentifierMatcher::class);
        $this->shouldHaveType(ViewMatcherInterface::class);
    }

    static function initialize(Repository $repository, array $matchingConfig): FieldDefinitionIdentifierMatcher
    {
        $matcher = new FieldDefinitionIdentifierMatcher();
        $matcher->setRepository($repository);
        $matcher->setMatchingConfig($matchingConfig);

        return  $matcher;
    }

    function let(Repository $repository, ContentTypeService $contentTypeService)
    {
        $repository->getContentTypeService()->willReturn($contentTypeService);
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION)->willReturn($this->createContentTypeWithoutFieldDefinition());
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID_WITH_FIELD_DEFINITION)->willReturn($this->createMatchingContentTypeWithFieldDefinition());
        $this->beConstructedThrough([$this, 'initialize'], [$repository, [self::FIELD_DEFINITION_IDENTIFIER]]);
    }

    function it_does_not_match_if_field_definition_identifier_does_not_exist()
    {
        $view = $this->buildView(self::CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION);
        $this->match($view)->shouldBe(false);
    }

    function it_matches_if_field_definition_identifier_matches()
    {
        $view = $this->buildView(self::CONTENT_TYPE_ID_WITH_FIELD_DEFINITION);
        $this->match($view)->shouldBe(true);
    }

    private function buildView($contentTypeId): ContentView
    {
        $view = new ContentView();
        $view->setContent(
            new Content([
                'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo(['contentTypeId' => $contentTypeId]),
                ]),
            ])
        );
        $view->addParameters(['fieldIdentifier' => self::FIELD_DEFINITION_IDENTIFIER]);

        return $view;
    }

    private function createContentTypeWithoutFieldDefinition(): ContentType
    {
        return $this->createContentType(
            self::CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION,
            self::CONTENT_TYPE_IDENTIFIER_WITHOUT_FIELD_DEFINITION,
            false
        );
    }

    private function createMatchingContentTypeWithFieldDefinition(): ContentType
    {
        return $this->createContentType(
            self::CONTENT_TYPE_ID_WITH_FIELD_DEFINITION,
            self::CONTENT_TYPE_IDENTIFIER_WITH_FIELD_DEFINITION,
            true
        );
    }

    private function createContentType(int $contentTypeId, string $contentTypeIdentifier, bool $withFieldDefinition): ContentType
    {
        $fieldDefinitions = [];
        if ($withFieldDefinition === true) {
            $fieldDefinitions[] = new FieldDefinition(['identifier' => self::FIELD_DEFINITION_IDENTIFIER]);
        }
        $fieldDefinitions = new FieldDefinitionCollection($fieldDefinitions);

        return new ContentType(
            [
                'id' => $contentTypeId,
                'identifier' => $contentTypeIdentifier,
                'fieldDefinitions' => $fieldDefinitions,
            ]
        );
    }
}
