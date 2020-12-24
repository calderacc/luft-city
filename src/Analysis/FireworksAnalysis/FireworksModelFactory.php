<?php declare(strict_types=1);

namespace App\Analysis\FireworksAnalysis;

use App\Entity\Data;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FireworksModelFactory implements FireworksModelFactoryInterface
{
    /** @var RegistryInterface $registry */
    protected $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function convert(array $dataResult): array
    {
        $resultList = [];

        /** @var Data $data */
        foreach ($dataResult as $data) {
            $stationId = $data->getStation()->getId();

            if (!array_key_exists($stationId, $resultList) || $resultList[$stationId]->getData()->getValue() < $data->getValue()) {
                $resultList[$stationId] = new FireworksModel($data->getStation(), $data, 0);
            }
        }

        return $resultList;
    }
}
