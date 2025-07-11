<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\FieldType\Query;

use Ibexa\Core\FieldType\Value as BaseValue;

final class Value extends BaseValue
{
    public function __construct(public string $text = '')
    {
        parent::__construct();
    }

    public function __toString(): string
    {
        return $this->text;
    }
}
