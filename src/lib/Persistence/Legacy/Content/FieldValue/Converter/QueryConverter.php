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
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $value->data;
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \Ibexa\Contracts\Core\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = $value->dataText;
        $fieldValue->sortKey = $value->sortKeyString;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataText1 = $fieldDef->fieldTypeConstraints->fieldSettings['QueryType'];
        $storageDef->dataText2 = $fieldDef->fieldTypeConstraints->fieldSettings['ReturnedType'];
        $storageDef->dataText5 = \json_encode($fieldDef->fieldTypeConstraints->fieldSettings['Parameters']);
        $storageDef->dataInt1 = (int)$fieldDef->fieldTypeConstraints->fieldSettings['EnablePagination'];
        $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->fieldSettings['ItemsPerPage'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = [
            'QueryType' => $storageDef->dataText1 ?: null,
            'ReturnedType' => $storageDef->dataText2 ?: null,
            'Parameters' => \json_decode($storageDef->dataText5, true),
            'EnablePagination' => (bool)$storageDef->dataInt1,
            'ItemsPerPage' => $storageDef->dataInt2,
        ];
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }
}
