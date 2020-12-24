<?php declare(strict_types=1);

namespace App\Pollution\DataFinder;

use App\Entity\Data;
use App\Pollution\StationCache\StationCacheInterface;
use Elastica\Result;

class DataConverter implements DataConverterInterface
{
    protected StationCacheInterface $stationCache;

    public function __construct(StationCacheInterface $stationCache)
    {
        $this->stationCache = $stationCache;
    }

    public function convert(Result $elasticResult): ?Data
    {
        $data = new Data();

        $station = $this->stationCache->getStationByCode($elasticResult->getData()['station']['stationCode']);

        if (!$station) {
            return null;
        }

        $data
            ->setValue($elasticResult->getData()['value'])
            ->setPollutant($elasticResult->getData()['pollutant'])
            ->setStation($station)
            ->setDateTime(new \DateTime($elasticResult->getData()['dateTime']))
        ;

        return $data;
    }

    public function convertArray(array $result): ?Data
    {
        $data = new Data();

        $station = $this->stationCache->getStationByCode($result['station']['stationCode']);

        if (!$station) {
            return null;
        }

        $data
            ->setValue($result['value'])
            ->setPollutant($result['pollutant'])
            ->setStation($station)
            ->setDateTime(new \DateTime($result['dateTime']))
        ;

        return $data;
    }
}