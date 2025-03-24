<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace spec\Ibexa\FieldTypeQuery;

use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\FieldTypeQuery\ExceptionSafeQueryFieldService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExceptionSafeQueryFieldServiceSpec extends ObjectBehavior
{
    public function let(QueryFieldServiceInterface $queryFieldService): void
    {
        $arguments = [
            Argument::type(Content::class),
            Argument::type('string'),
        ];
        $queryFieldService->countContentItems(...$arguments)->willThrow('Exception');
        $queryFieldService->loadContentItems(...$arguments)->willThrow('Exception');

        $arguments[] = Argument::type('int');
        $arguments[] = Argument::type('int');
        $queryFieldService->loadContentItemsSlice(...$arguments)->willThrow('Exception');

        $this->beConstructedWith($queryFieldService);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ExceptionSafeQueryFieldService::class);
    }

    public function it_should_return_empty_results_on_count_content_items(): void
    {
        $result = $this->countContentItems(new Content([]), 'any');
        $result->shouldBe(0);
    }

    public function it_should_return_empty_results_on_load_content_items(): void
    {
        $result = $this->loadContentItems(new Content([]), 'any');
        $result->shouldBe([]);
    }

    public function it_should_return_empty_results_on_load_content_items_slice(): void
    {
        $result = $this->loadContentItemsSlice(new Content([]), 'any', 0, 5);
        $result->shouldBe([]);
    }
}
