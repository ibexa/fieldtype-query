<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace spec\Ibexa\FieldTypeQuery\GraphQL;

use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\FieldTypeQuery\GraphQL\QueryFieldResolver;
use Ibexa\GraphQL\Value\Field;
use PhpSpec\ObjectBehavior;

final class QueryFieldResolverSpec extends ObjectBehavior
{
    public const string FIELD_DEFINITION_IDENTIFIER = 'test';

    public function let(QueryFieldServiceInterface $queryFieldService): void
    {
        $this->beConstructedWith($queryFieldService);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(QueryFieldResolver::class);
    }

    public function it_resolves_a_query_field(QueryFieldServiceInterface $queryFieldService): void
    {
        $content = new Content();
        $field = new Field(['fieldDefIdentifier' => self::FIELD_DEFINITION_IDENTIFIER, 'value' => new \stdClass()]);
        $queryFieldService->loadContentItems($content, self::FIELD_DEFINITION_IDENTIFIER)->willReturn([]);
        $this->resolveQueryField($field, $content)->shouldReturn([]);
    }
}
