<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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

final class FieldDefinitionIdentifierMatcherSpec extends ObjectBehavior
{
    private const int CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION = 2;
    private const string CONTENT_TYPE_IDENTIFIER_WITHOUT_FIELD_DEFINITION = 'type_matching_without_field_def';

    private const int CONTENT_TYPE_ID_WITH_FIELD_DEFINITION = 3;
    private const string CONTENT_TYPE_IDENTIFIER_WITH_FIELD_DEFINITION = 'type_matching_with_field_def';

    public const string FIELD_DEFINITION_IDENTIFIER = 'field_definition';

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FieldDefinitionIdentifierMatcher::class);
        $this->shouldHaveType(ViewMatcherInterface::class);
    }

    public static function initialize(Repository $repository, array $matchingConfig): FieldDefinitionIdentifierMatcher
    {
        $matcher = new FieldDefinitionIdentifierMatcher();
        $matcher->setRepository($repository);
        $matcher->setMatchingConfig($matchingConfig);

        return  $matcher;
    }

    public function let(Repository $repository, ContentTypeService $contentTypeService): void
    {
        $repository->getContentTypeService()->willReturn($contentTypeService);
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION)->willReturn($this->createContentTypeWithoutFieldDefinition());
        $contentTypeService->loadContentType(self::CONTENT_TYPE_ID_WITH_FIELD_DEFINITION)->willReturn($this->createMatchingContentTypeWithFieldDefinition());
        $this->beConstructedThrough([$this, 'initialize'], [$repository, [self::FIELD_DEFINITION_IDENTIFIER]]);
    }

    public function it_does_not_match_if_field_definition_identifier_does_not_exist(): void
    {
        $view = $this->buildView(self::CONTENT_TYPE_ID_WITHOUT_FIELD_DEFINITION);
        $this->match($view)->shouldBe(false);
    }

    public function it_matches_if_field_definition_identifier_matches(): void
    {
        $view = $this->buildView(self::CONTENT_TYPE_ID_WITH_FIELD_DEFINITION);
        $this->match($view)->shouldBe(true);
    }

    private function buildView(int $contentTypeId): ContentView
    {
        $view = new ContentView();
        $view->setContent(
            new Content([
                'versionInfo' => new VersionInfo([
                    'contentInfo' => new ContentInfo([
                        'contentTypeId' => $contentTypeId,
                        'contentType' => new ContentType([
                            'id' => $contentTypeId,
                            'identifier' => 'foo_content_type',
                        ]),
                    ]),
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
