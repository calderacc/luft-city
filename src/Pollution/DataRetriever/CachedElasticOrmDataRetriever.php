<?php declare(strict_types=1);

namespace App\Pollution\DataRetriever;

use App\Entity\Data;
use App\Entity\Station;
use App\Pollution\DataCache\DataCacheInterface;
use App\Pollution\DataCache\KeyGenerator;
use Caldera\GeoBasic\Coord\CoordInterface;
use FOS\ElasticaBundle\Finder\FinderInterface;

class CachedElasticOrmDataRetriever implements DataRetrieverInterface
{
    /** @var FinderInterface $dataFinder */
    protected $dataFinder;

    /** @var DataCacheInterface $dataCache */
    protected $dataCache;

    public function __construct(DataCacheInterface $dataCache, FinderInterface $dataFinder)
    {
        $this->dataFinder = $dataFinder;
        $this->dataCache = $dataCache;
    }

    public function retrieveDataForCoord(CoordInterface $coord, int $pollutantId, \DateTime $fromDateTime = null, \DateInterval $dateInterval = null, float $maxDistance = 20.0, int $maxResults = 750): array
    {
        if ($coord instanceof Station) {
            $stationList = [$coord];
        } else {
            $stationList = $this->getStationList($coord, $maxDistance, 750); // @TODO: get rid of working set size in Pollution Data Factory
        }
        
        $dataList = [];

        /** @var Station $station */
        foreach ($stationList as $station) {
            $key = KeyGenerator::generateKeyForStationAndPollutant($station, $pollutantId);

            /** @var Data $data */
            if ($data = $this->dataCache->getData($key)) {
                $dataList[] = $data;
            }
        }

        return $dataList;
    }

    protected function getStationList(CoordInterface $coord, float $maxDistance = 20.0, int $maxResults = 750): array
    {
        if ($coord instanceof Station) {
            $stationQuery = new \Elastica\Query\Term(['id' => $coord->getId()]);
        } else {
            $stationQuery = new \Elastica\Query\GeoDistance('pin', [
                'lat' => $coord->getLatitude(),
                'lon' => $coord->getLongitude(),
            ],
                sprintf('%fkm', $maxDistance));
        }

        $query = new \Elastica\Query($stationQuery);

        $query
            ->addSort([
                '_geo_distance' => [
                    'pin' => [
                        'lat' => $coord->getLatitude(),
                        'lon' => $coord->getLongitude()
                    ],
                    'order' => 'asc',
                    'unit' => 'km',
                ]
            ]);

        $query->setSize($maxResults);

        return $this->dataFinder->find($query);
    }
}
