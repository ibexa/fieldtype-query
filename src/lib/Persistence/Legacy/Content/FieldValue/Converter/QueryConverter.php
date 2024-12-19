<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\Persistence\Legacy\Content\FieldValue\Converter;

use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;

class QueryConverter implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content\FieldValue $value
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void
    {
        $storageFieldValue->dataText = $value->data;
        $storageFieldValue->sortKeyString = (string)$value->sortKey;
    }

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void
    {
        $fieldValue->data = $value->dataText;
        $fieldValue->sortKey = $value->sortKeyString;
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $storageDef->dataText1 = $fieldDef->fieldTypeConstraints->fieldSettings['QueryType'];
        $storageDef->dataText2 = $fieldDef->fieldTypeConstraints->fieldSettings['ReturnedType'];
        $storageDef->dataText5 = \json_encode($fieldDef->fieldTypeConstraints->fieldSettings['Parameters']) ?: '';
        $storageDef->dataInt1 = (int)$fieldDef->fieldTypeConstraints->fieldSettings['EnablePagination'];
        $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->fieldSettings['ItemsPerPage'];
    }

    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = [
            'QueryType' => $storageDef->dataText1 ?: null,
            'ReturnedType' => $storageDef->dataText2 ?: null,
            'Parameters' => \json_decode($storageDef->dataText5, true),
            'EnablePagination' => (bool)$storageDef->dataInt1,
            'ItemsPerPage' => $storageDef->dataInt2,
        ];
    }

    public function getIndexColumn()
    {
        return 'sort_key_string';
    }
}
