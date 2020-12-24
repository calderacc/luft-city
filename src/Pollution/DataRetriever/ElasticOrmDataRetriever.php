<?php declare(strict_types=1);

namespace App\Pollution\DataRetriever;

use App\Entity\Station;
use Caldera\GeoBasic\Coord\CoordInterface;
use FOS\ElasticaBundle\Finder\FinderInterface;

class ElasticOrmDataRetriever implements DataRetrieverInterface
{
    /** @var FinderInterface $dataFinder */
    protected $dataFinder;

    public function __construct(FinderInterface $dataFinder)
    {
        $this->dataFinder = $dataFinder;
    }

    public function retrieveDataForCoord(CoordInterface $coord, int $pollutantId, \DateTime $fromDateTime = null, \DateInterval $dateInterval = null, float $maxDistance = 20.0, int $maxResults = 750): array
    {
        if ($coord instanceof Station) {
            $stationQuery = new \Elastica\Query\Nested();
            $stationQuery->setPath('station');
            $stationQuery->setQuery(new \Elastica\Query\Term(['station.id' => $coord->getId()]));
        } else {
            $stationGeoQuery = new \Elastica\Query\GeoDistance('station.pin', [
                'lat' => $coord->getLatitude(),
                'lon' => $coord->getLongitude(),
            ],
                sprintf('%fkm', $maxDistance));

            $stationQuery = new \Elastica\Query\Nested();
            $stationQuery->setPath('station');
            $stationQuery->setQuery($stationGeoQuery);
        }

        $pollutantQuery = new \Elastica\Query\Term(['pollutant' => $pollutantId]);

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery
            ->addMust($pollutantQuery)
            ->addMust($stationQuery);

        if ($fromDateTime && $dateInterval) {
            $untilDateTime = clone $fromDateTime;
            $untilDateTime->add($dateInterval);

            $dateTimeQuery = new \Elastica\Query\Range('dateTime', [
                'gt' => $fromDateTime->format('Y-m-d H:i:s'),
                'lte' => $untilDateTime->format('Y-m-d H:i:s'),
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ]);

            $boolQuery->addMust($dateTimeQuery);
        }

        $query = new \Elastica\Query($boolQuery);

        $query
            ->addSort([
                '_geo_distance' => [
                    'station.pin' => [
                        'lat' => $coord->getLatitude(),
                        'lon' => $coord->getLongitude()
                    ],
                    'order' => 'asc',
                    'unit' => 'km',
                    'nested_path' => 'station',
                ]
            ])
            ->addSort(['dateTime' => 'desc']);

        $query->setSize($maxResults);

        return $this->dataFinder->find($query);
    }
}
