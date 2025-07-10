<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\FieldType\Mapper;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<mixed|null, string|null>
 */
final class ParametersTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Yaml::dump($value);
    }

    public function reverseTransform(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return Yaml::parse($value);
    }
}
