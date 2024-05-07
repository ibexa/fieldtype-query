<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\FieldType\Query;

use Ibexa\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * Text content.
     *
     * @var string
     */
    public $text;

    /**
     * Construct a new Value object and initialize it $text.
     *
     * @param string $text
     */
    public function __construct($text = '')
    {
        $this->text = $text;
    }

    /**
     * @see \Ibexa\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->text;
    }
}

class_alias(Value::class, 'EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Query\Value');
