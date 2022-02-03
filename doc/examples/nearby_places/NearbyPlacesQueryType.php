<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace App\QueryType;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Core\QueryType\QueryType;

class NearbyPlacesQueryType implements QueryType
{
    public function getQuery(array $parameters = [])
    {
        return new Query([
            'filter' => new Criterion\LogicalAnd([
                new Criterion\ContentTypeIdentifier('place'),
                new Criterion\MapLocationDistance(
                    'location',
                    Criterion\Operator::LTE,
                    $parameters['distance'],
                    $parameters['latitude'],
                    $parameters['longitude']
                )
            ]),
        ]);
    }

    public function getSupportedParameters()
    {
        return ['distance', 'latitude', 'longitude'];
    }

    public static function getName()
    {
        return 'NearbyPlaces';
    }
}
