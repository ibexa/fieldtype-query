<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace spec\Ibexa\FieldTypeQuery\GraphQL;

use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\FieldTypeQuery\GraphQL\QueryFieldResolver;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\GraphQL\Value\Field;
use PhpSpec\ObjectBehavior;

class QueryFieldResolverSpec extends ObjectBehavior
{
    const FIELD_DEFINITION_IDENTIFIER = 'test';

    function let(QueryFieldServiceInterface $queryFieldService)
    {
        $this->beConstructedWith($queryFieldService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryFieldResolver::class);
    }

    function it_resolves_a_query_field(QueryFieldServiceInterface $queryFieldService)
    {
        $content = new Content();
        $field = new Field(['fieldDefIdentifier' => self::FIELD_DEFINITION_IDENTIFIER, 'value' => new \stdClass()]);
        $queryFieldService->loadContentItems($content, self::FIELD_DEFINITION_IDENTIFIER)->willReturn([]);
        $this->resolveQueryField($field, $content)->shouldReturn([]);
    }
}
