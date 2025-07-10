<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\FieldTypeQuery\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds the configuration for the query field type to the ibexa.graphql.schema.content.mapping.field_definition_type
 * configuration variable.
 */
final class ConfigurableFieldDefinitionMapperPass implements CompilerPassInterface
{
    public const string PARAMETER = 'ibexa.graphql.schema.content.mapping.field_definition_type';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(self::PARAMETER)) {
            return;
        }

        $parameter = $container->getParameter(self::PARAMETER);
        if (!is_array($parameter)) {
            return;
        }
        $parameter['ibexa_content_query'] = [
            'definition_type' => 'QueryFieldDefinition',
            'value_resolver' => 'resolver("QueryFieldValue", [field, content])',
        ];

        $container->setParameter(self::PARAMETER, $parameter);
    }
}
