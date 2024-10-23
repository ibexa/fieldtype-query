<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\FieldTypeQuery\GraphQL;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\FieldTypeQuery\GraphQL\ContentQueryFieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\NameHelper;
use PhpSpec\ObjectBehavior;

class ContentQueryFieldDefinitionMapperSpec extends ObjectBehavior
{
    public const FIELD_IDENTIFIER = 'test';
    public const FIELD_TYPE_IDENTIFIER = 'ezcontentquery';
    public const RETURNED_CONTENT_TYPE_IDENTIFIER = 'folder';
    public const GRAPHQL_TYPE = 'FolderContent';

    public function let(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ) {
        $contentType = new ContentType(['identifier' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]);

        $contentTypeService
            ->loadContentTypeByIdentifier(self::RETURNED_CONTENT_TYPE_IDENTIFIER)
            ->willReturn($contentType);

        $nameHelper
            ->itemName($contentType)
            ->willReturn(self::GRAPHQL_TYPE);

        $this->beConstructedWith($innerMapper, $nameHelper, $contentTypeService, self::FIELD_TYPE_IDENTIFIER);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ContentQueryFieldDefinitionMapper::class);
        $this->shouldHaveType(FieldDefinitionMapper::class);
    }

    public function it_returns_as_value_type_the_configured_ContentType_for_query_field_definitions(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('[' . self::GRAPHQL_TYPE . ']');
    }

    public function it_delegates_value_type_to_the_inner_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldValueType($fieldDefinition)->willReturn('SomeType');
        $this
            ->mapToFieldValueType($fieldDefinition)
            ->shouldBe('SomeType');
    }

    public function it_returns_the_correct_field_definition_GraphQL_type(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('ContentQueryFieldDefinition');
    }

    public function it_delegates_field_definition_type_to_the_parent_mapper_for_a_non_query_field_definition(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->getLambdaFieldDefinition();
        $innerMapper->mapToFieldDefinitionType($fieldDefinition)->willReturn('FieldValue');
        $this
            ->mapToFieldDefinitionType($fieldDefinition)
            ->shouldBe('FieldValue');
    }

    public function it_maps_the_field_value_when_pagination_is_disabled(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition();
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueResolver($fieldDefinition)
            ->shouldBe('@=resolver("QueryFieldValue", [field, content])');
    }

    public function it_maps_the_field_value_when_pagination_is_enabled(FieldDefinitionMapper $innerMapper)
    {
        $fieldDefinition = $this->fieldDefinition(true);
        $innerMapper->mapToFieldValueResolver($fieldDefinition)->shouldNotBeCalled();
        $this
            ->mapToFieldValueResolver($fieldDefinition)
            ->shouldBe('@=resolver("QueryFieldValueConnection", [args, field, content])');
    }

    /**
     * @param bool $enablePagination
     *
     * @return \Ibexa\Core\Repository\Values\ContentType\FieldDefinition
     */
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

    /**
     * @return \Ibexa\Core\Repository\Values\ContentType\FieldDefinition
     */
    protected function getLambdaFieldDefinition(): \Ibexa\Core\Repository\Values\ContentType\FieldDefinition
    {
        return new FieldDefinition(['fieldTypeIdentifier' => 'lambda']);
    }
}
