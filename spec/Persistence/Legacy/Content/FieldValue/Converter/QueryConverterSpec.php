<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\FieldTypeQuery\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\FieldTypeQuery\Persistence\Legacy\Content\FieldValue\Converter\QueryConverter;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

final class QueryConverterSpec extends ObjectBehavior
{
    public const array PARAMETERS = ['param1' => 'value1', 'param2' => 'value2'];
    public const string QUERY_TYPE = 'SomeQueryType';
    public const string RETURNED_TYPE = 'folder';
    public const bool ENABLE_PAGINATION = true;
    public const int ITEMS_PER_PAGE = 10;

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(QueryConverter::class);
    }

    public function getFieldDefinition(): FieldDefinition
    {
        $fieldDefinition = new FieldDefinition();
        $fieldDefinition->fieldTypeConstraints->fieldSettings = [
            'QueryType' => self::QUERY_TYPE,
            'ReturnedType' => self::RETURNED_TYPE,
            'Parameters' => self::PARAMETERS,
            'EnablePagination' => self::ENABLE_PAGINATION,
            'ItemsPerPage' => self::ITEMS_PER_PAGE,
        ];

        return $fieldDefinition;
    }

    public function getStorageDefinition(): StorageFieldDefinition
    {
        $fieldDefinition = new StorageFieldDefinition();
        $fieldDefinition->dataText5 = \json_encode(self::PARAMETERS);
        $fieldDefinition->dataText1 = self::QUERY_TYPE;
        $fieldDefinition->dataText2 = self::RETURNED_TYPE;
        $fieldDefinition->dataInt1 = (int)self::ENABLE_PAGINATION;
        $fieldDefinition->dataInt2 = self::ITEMS_PER_PAGE;

        return $fieldDefinition;
    }

    public function it_stores_Parameters_in_dataText5_in_json_format(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText5, \json_encode(self::PARAMETERS));
    }

    public function it_stores_QueryType_in_dataText1(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText1, self::QUERY_TYPE);
    }

    public function it_stores_ReturnedType_in_dataText2(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText2, self::RETURNED_TYPE);
    }

    public function it_stores_EnablePagination_in_dataInt1(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataInt1, self::ENABLE_PAGINATION);
    }

    public function it_stores_ItemsPerPage_in_dataInt2(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataInt2, self::ITEMS_PER_PAGE);
    }

    public function it_reads_Parameters_from_dataText5_in_json_format(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText5, \json_encode(self::PARAMETERS));
    }

    public function it_reads_QueryType_from_dataText1(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText1, self::QUERY_TYPE);
    }

    public function it_reads_ReturnedType_from_dataText2(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataText2, self::RETURNED_TYPE);
    }

    public function it_reads_EnablePagination_from_dataInt1(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataInt1, self::ENABLE_PAGINATION);
    }

    public function it_reads_ItemsPerPage_from_dataInt2(): void
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->toStorageFieldDefinition($this->getFieldDefinition(), $storageFieldDefinition);
        Assert::eq($storageFieldDefinition->dataInt2, self::ITEMS_PER_PAGE);
    }
}
