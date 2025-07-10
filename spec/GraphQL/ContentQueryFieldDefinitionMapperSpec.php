<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\FieldTypeQuery\GraphQL;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\FieldTypeQuery\GraphQL\ContentQueryFieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\NameHelper;
use PhpSpec\ObjectBehavior;

final class ContentQueryFieldDefinitionMapperSpec extends ObjectBehavior
{
    public const string FIELD_IDENTIFIER = 'test';
    public const string FIELD_TYPE_IDENTIFIER = 'ibexa_content_query';
    public const string RETURNED_CONTENT_TYPE_IDENTIFIER = 'folder';
    public const string GRAPHQL_TYPE = 'FolderContent';

    public function let(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ): void {
        $contentType = new ContentType(['identifier' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]);

        $contentTypeService
            ->loadContentTypeByIdentifier(self::RETURNED_CONTENT_TYPE_IDENTIFIER)
            ->willReturn($contentType);

        $nameHelper
            ->itemName($contentType)
            ->willReturn(self::GRAPHQL_TYPE);

        $this->beConstructedWith($innerMapper, $nameHelper, $contentTypeService, self::FIELD_TYPE_IDENTIFIER);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ContentQueryFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    public function it_returns_as_value_type_the_configured_ContentType_for_query_field_definitions(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('[' . self::GRAPHQL_TYPE . ']');
    }

    public function it_delegates_value_type_to_the_inner_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->willReturn('SomeType');
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('SomeType');
    }

    public function it_returns_the_correct_field_definition_GraphQL_type(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('ContentQueryFieldDefinition');
    }

    public function it_delegates_field_definition_type_to_the_parent_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->willReturn('FieldValue');
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('FieldValue');
    }

    public function it_maps_the_field_value_when_pagination_is_disabled(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueResolver($fieldDefinition)
            ->shouldBe('@=resolver("QueryFieldValue", [field, content])');
    }

    public function it_maps_the_field_value_when_pagination_is_enabled(FieldDefinitionMapper $innerMapper): void
    {
        $fieldDefinition = $this->fieldDefinition(true);
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueResolver($fieldDefinition)
            ->shouldBe('@=resolver("QueryFieldValueConnection", [args, field, content])');
    }

    private function fieldDefinition(bool $enablePagination = false): FieldDefinition
    {
        return new FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'fieldTypeIdentifier' => self::FIELD_TYPE_IDENTIFIER,
            'fieldSettings' => [
                'ReturnedType' => self::RETURNED_CONTENT_TYPE_IDENTIFIER,
                'EnablePagination' => $enablePagination,
             ],
        ]);
    }

    protected function getLambdaFieldDefinition(): FieldDefinition
    {
        return new FieldDefinition(['fieldTypeIdentifier' => 'lambda']);
    }
}
