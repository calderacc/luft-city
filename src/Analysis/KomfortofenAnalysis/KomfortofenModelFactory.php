<?php declare(strict_types=1);

namespace App\Analysis\KomfortofenAnalysis;

use App\Entity\Data;
use App\Pollution\DataFinder\DataConverterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class KomfortofenModelFactory implements KomfortofenModelFactoryInterface
{
    protected DataConverterInterface $dataConverter;

    public function __construct(DataConverterInterface $dataConverter)
    {
        $this->dataConverter = $dataConverter;
    }

    public function convert(array $buckets): array
    {
        $resultList = [];

        foreach ($buckets as $bucket) {
            $slope = array_pop($bucket['derivative_agg']);

            foreach ($bucket['top_hits_agg']['hits']['hits'] as $hit) {
                /** @var Data $data */
                $data = $this->dataConverter->convertArray($hit['_source']);

                $resultList[] = new KomfortofenModel($data->getStation(), $data, $slope);
            }
        }

        return $this->sortResultList($resultList);
    }

    /**
     * @TODO this should be done in elasticsearch
     */
    protected function sortResultList(array $resultList = []): array
    {
        usort($resultList, function(KomfortofenModel $a, KomfortofenModel $b): int
        {
            if ($a->getSlope() === $b->getSlope()) {
                return 0;
            }
            return ($a->getSlope() > $b->getSlope()) ? -1 : 1;
        });

        return $resultList;
    }
}
