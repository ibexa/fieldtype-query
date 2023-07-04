<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\FieldTypeQuery\FieldType\Query;

use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\FieldType\FieldType;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\FieldType\Value as BaseValue;
use Ibexa\Core\QueryType\QueryTypeRegistry;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

final class Type extends FieldType implements TranslationContainerInterface
{
    protected $validatorConfigurationSchema = [];

    protected $settingsSchema = [
        'QueryType' => ['type' => 'string', 'default' => ''],
        'Parameters' => ['type' => 'array', 'default' => []],
        'ReturnedType' => ['type' => 'string', 'default' => ''],
        'EnablePagination' => ['type' => 'boolean', 'default' => true],
        'ItemsPerPage' => ['type' => 'integer', 'default' => 10],
    ];

    /** @var \Ibexa\Core\QueryType\QueryTypeRegistry */
    private $queryTypeRegistry;

    /** @var \Ibexa\Contracts\Core\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var string */
    private $identifier;

    public function __construct(QueryTypeRegistry $queryTypeRegistry, ContentTypeService $contentTypeService, string $identifier)
    {
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->contentTypeService = $contentTypeService;
        $this->identifier = $identifier;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \Ibexa\Contracts\Core\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = [];

        return $validationErrors;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \Ibexa\Core\FieldType\TextLine\Value $fieldValue The field value for which an action is performed
     *
     * @return \Ibexa\Contracts\Core\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        return [];
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \Ibexa\FieldTypeQuery\FieldType\Query\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return (string)$value->text;
    }

    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        return false;
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_string($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \Ibexa\Core\FieldType\TextLine\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!is_string($value->text)) {
            throw new InvalidArgumentType(
                '$value->text',
                'string',
                $value->text
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \Ibexa\Core\FieldType\TextLine\Value $value
     *
     * @return array
     */
    protected function getSortInfo(BaseValue $value)
    {
        return $this->transformationProcessor->transformByGroup((string)$value, 'lowercase');
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \Ibexa\Core\FieldType\TextLine\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \Ibexa\Core\FieldType\TextLine\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $value->text;
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    public function validateFieldSettings($fieldSettings)
    {
        $errors = [];

        if (isset($fieldSettings['QueryType']) && $fieldSettings['QueryType'] !== '') {
            try {
                $this->queryTypeRegistry->getQueryType($fieldSettings['QueryType']);
            } catch (InvalidArgumentException $e) {
                $errors[] = new ValidationError('The selected query type does not exist');
            }
        }

        if (isset($fieldSettings['ReturnedType']) && $fieldSettings['ReturnedType'] !== '') {
            try {
                $this->contentTypeService->loadContentTypeByIdentifier($fieldSettings['ReturnedType']);
            } catch (NotFoundException $e) {
                $errors[] = new ValidationError('The selected returned type could not be loaded');
            }
        }

        if (isset($fieldSettings['EnablePagination'])) {
            if (!is_bool($fieldSettings['EnablePagination'])) {
                $errors[] = new ValidationError('EnablePagination is not a boolean');
            }
        }

        if (isset($fieldSettings['ItemsPerPage'])) {
            if (!is_numeric($fieldSettings['ItemsPerPage'])) {
                $errors[] = new ValidationError('ItemsPerPage is not an integer');
            }
        }

        if (isset($fieldSettings['Parameters'])) {
            if (!is_array($fieldSettings['Parameters'])) {
                $errors[] = new ValidationError('Parameters is not a valid YAML string');
            }
        }

        return $errors;
    }

    public static function getTranslationMessages(): array
    {
        return [
            Message::create('ezcontentquery.name', 'fieldtypes')->setDesc('Content query'),
        ];
    }
}

class_alias(Type::class, 'EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Query\Type');
