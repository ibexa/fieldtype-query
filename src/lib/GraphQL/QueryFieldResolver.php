<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery\GraphQL;

use GraphQL\Executor\Promise\Promise;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

final readonly class QueryFieldResolver
{
    public function __construct(private QueryFieldServiceInterface $queryFieldService)
    {
    }

    /**
     * @return iterable<\Ibexa\Contracts\Core\Repository\Values\Content\Content>
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function resolveQueryField(Field $field, Content $content): iterable
    {
        return $this->queryFieldService->loadContentItems($content, $field->fieldDefIdentifier);
    }

    /**
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\Output\Connection<\Ibexa\Contracts\Core\Repository\Values\Content\Content>|null
     */
    public function resolveQueryFieldConnection(Argument $args, ?Field $field, Content $content): Promise|Connection|null
    {
        if ($field === null) {
            return null;
        }

        if (!isset($args['first'])) {
            $args['first'] = $this->queryFieldService->getPaginationConfiguration($content, $field->fieldDefIdentifier);
        }

        $paginator = new Paginator(function ($offset, $limit) use ($content, $field) {
            return $this->queryFieldService->loadContentItemsSlice($content, $field->fieldDefIdentifier, $offset, $limit);
        });

        return $paginator->auto(
            $args,
            function () use ($content, $field): int {
                return $this->queryFieldService->countContentItems($content, $field->fieldDefIdentifier);
            }
        );
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<array{name: string, value: mixed}>
     */
    public function resolveQueryFieldDefinitionParameters(array $parameters): array
    {
        $return = [];
        foreach ($parameters as $name => $value) {
            $return[] = ['name' => $name, 'value' => $value];
        }

        return $return;
    }
}
