<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\FieldTypeQuery\FieldType\Mapper;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Yaml\Yaml;

final class ParametersTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value === null) {
            return null;
        }

        return Yaml::dump($value, 2, 4);
    }

    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }

        return Yaml::parse($value);
    }
}

class_alias(ParametersTransformer::class, 'EzSystems\EzPlatformQueryFieldType\eZ\FieldType\Mapper\ParametersTransformer');
