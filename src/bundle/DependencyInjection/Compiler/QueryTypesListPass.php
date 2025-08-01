<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\FieldTypeQuery\DependencyInjection\Compiler;

use Ibexa\Core\QueryType\ArrayQueryTypeRegistry;
use Ibexa\FieldTypeQuery\FieldType\Mapper\QueryFormMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

final class QueryTypesListPass implements CompilerPassInterface
{
    private CamelCaseToSnakeCaseNameConverter $nameConverter;

    public function __construct()
    {
        $this->nameConverter = new CamelCaseToSnakeCaseNameConverter();
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(ArrayQueryTypeRegistry::class) || !$container->has(QueryFormMapper::class)) {
            return;
        }

        $queryTypes = [];
        foreach ($container->getDefinition(ArrayQueryTypeRegistry::class)->getMethodCalls() as $methodCall) {
            if ($methodCall[0] === 'addQueryType') {
                $queryTypes[] = $methodCall[1][0];
            } elseif ($methodCall[0] === 'addQueryTypes') {
                foreach (array_keys($methodCall[1][0]) as $queryTypeIdentifier) {
                    $queryTypes[$this->buildQueryTypeName($queryTypeIdentifier)] = $queryTypeIdentifier;
                }
            }
        }

        $formMapperDefinition = $container->getDefinition(QueryFormMapper::class);
        $formMapperDefinition->setArgument('$queryTypes', $queryTypes);
    }

    /**
     * Builds a human-readable name out of a query type identifier.
     */
    private function buildQueryTypeName(string $queryTypeIdentifier): string
    {
        return ucfirst(
            str_replace('_', ' ', $this->nameConverter->normalize($queryTypeIdentifier))
        );
    }
}
