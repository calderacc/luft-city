<?php declare(strict_types=1);

namespace App\Pollution\DataPersister;

use App\Pollution\StationCache\StationCacheInterface;
use App\Pollution\Value\Value;
use App\Pollution\ValueDataConverter\ValueDataConverter;
use Elastica\Document;
use Elastica\Index;

class ElasticPersister implements PersisterInterface
{
    protected Index $index;
    protected StationCacheInterface $stationCache;

    public function __construct(Index $index, StationCacheInterface $stationCache)
    {
        $this->index = $index;
        $this->stationCache = $stationCache;
    }

    public function persistValues(array $values): PersisterInterface
    {
        $documentList = [];

        /** @var Value $value */
        foreach ($values as $value) {
            $document = new Document();

            $data = ValueDataConverter::convert($value);
            $station = $this->stationCache->getStationByCode($value->getStation());

            if (!$data || !$station) {
                continue;
            }

            $document
                ->setData([
                    'value' => $data->getValue(),
                    'pollutant' => $data->getPollutant(),
                    'dateTime' => $data->getDateTime()->format('Y-m-d H:i:s'),
                    'station' => [
                        'stationCode' => $station->getStationCode(),
                        'pin' => [
                            'lat' => $station->getLatitude(),
                            'lon' => $station->getLongitude(),
                        ],
                    ],
                ])
                ->setType('data');

            $documentList[] = $document;
        }

        if (0 !== count($documentList)) {
            $result = $this->index->addDocuments($documentList);

        }

        return $this;
    }

    public function getNewValueList(): array
    {
        return [];
    }

    public function reset(): PersisterInterface
    {
        return $this;
    }
}
