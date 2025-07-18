<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeQuery;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService;
use Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * Silences exceptions when they occur in query field service, for example due to field type misconfigurations.
 */
final class ExceptionSafeQueryFieldService implements QueryFieldServiceInterface, QueryFieldLocationService, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param \Ibexa\Contracts\FieldTypeQuery\QueryFieldServiceInterface&\Ibexa\Contracts\FieldTypeQuery\QueryFieldLocationService $inner
     */
    public function __construct(
        private readonly QueryFieldServiceInterface $inner,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    public function loadContentItems(Content $content, string $fieldDefinitionIdentifier): iterable
    {
        try {
            return $this->inner->loadContentItems($content, $fieldDefinitionIdentifier);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function countContentItems(Content $content, string $fieldDefinitionIdentifier): int
    {
        try {
            return $this->inner->countContentItems($content, $fieldDefinitionIdentifier);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return 0;
        }
    }

    public function loadContentItemsSlice(
        Content $content,
        string $fieldDefinitionIdentifier,
        int $offset,
        int $limit
    ): iterable {
        try {
            return $this->inner->loadContentItemsSlice($content, $fieldDefinitionIdentifier, $offset, $limit);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function getPaginationConfiguration(Content $content, string $fieldDefinitionIdentifier): int
    {
        return $this->inner->getPaginationConfiguration($content, $fieldDefinitionIdentifier);
    }

    public function loadContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): iterable
    {
        try {
            return $this->inner->loadContentItemsForLocation($location, $fieldDefinitionIdentifier);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function loadContentItemsSliceForLocation(
        Location $location,
        string $fieldDefinitionIdentifier,
        int $offset,
        int $limit
    ): iterable {
        try {
            return $this->inner->loadContentItemsSliceForLocation($location, $fieldDefinitionIdentifier, $offset, $limit);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return [];
        }
    }

    public function countContentItemsForLocation(Location $location, string $fieldDefinitionIdentifier): int
    {
        try {
            return $this->inner->countContentItemsForLocation($location, $fieldDefinitionIdentifier);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'exception' => $e,
            ]);

            return 0;
        }
    }
}
