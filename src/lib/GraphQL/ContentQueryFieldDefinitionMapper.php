<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\GraphQL;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;
use Ibexa\GraphQL\Schema\Domain\Content\NameHelper;

final class ContentQueryFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
{
    private NameHelper $nameHelper;

    private ContentTypeService $contentTypeService;

    private string $fieldTypeIdentifier;

    public function __construct(
        FieldDefinitionMapper $innerMapper,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService,
        string $fieldTypeIdentifier
    ) {
        parent::__construct($innerMapper);
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ($fieldSettings['EnablePagination']) {
            return $this->nameValueConnectionType($fieldSettings['ReturnedType']);
        } else {
            return '[' . $this->nameValueType($fieldSettings['ReturnedType']) . ']';
        }
    }

    public function mapToFieldValueResolver(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueResolver($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        if ($fieldSettings['EnablePagination']) {
            return '@=resolver("QueryFieldValueConnection", [args, field, content])';
        } else {
            return '@=resolver("QueryFieldValue", [field, content])';
        }
    }

    public function mapToFieldDefinitionType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldDefinitionType($fieldDefinition);
        }

        return 'ContentQueryFieldDefinition';
    }

    public function mapToFieldValueArgsBuilder(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueArgsBuilder($fieldDefinition);
        }

        if ($fieldDefinition->fieldSettings['EnablePagination']) {
            return 'Relay::Connection';
        } else {
            return null;
        }
    }

    protected function getFieldTypeIdentifier(): string
    {
        return $this->fieldTypeIdentifier;
    }

    private function nameValueType(string $typeIdentifier): string
    {
        return $this->nameHelper->itemName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }

    private function nameValueConnectionType(string $typeIdentifier): string
    {
        return $this->nameHelper->itemConnectionName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }
}
