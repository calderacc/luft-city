<?php declare(strict_types=1);

namespace App\Analysis\FireworksAnalysis;

use App\Air\Measurement\MeasurementInterface;
use Elastica\Query\BoolQuery;

class FireworksAnalysis extends AbstractFireworksAnalysis
{
    public function analyze(): array
    {
        $pm10Query = new \Elastica\Query\Term(['pollutant' => MeasurementInterface::MEASUREMENT_PM10]);
        //$pm25Query = new \Elastica\Query\Term(['pollutant' => PollutantInterface::POLLUTANT_PM25]);

        $pollutantQuery = new BoolQuery();
        $pollutantQuery->addShould($pm10Query);
        //$pollutantQuery->addShould($pm25Query);

        $dateTimeQuery = $this->createDateTimeQuery();

        $pollutionQuery = new \Elastica\Query\Range('value', ['gte' => 80]);

        $providerQuery = new \Elastica\Query\Term(['provider' => 'uba_de']);

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery
            ->addMust($pollutantQuery)
            ->addMust($dateTimeQuery)
            ->addMust($pollutionQuery)
            //->addMust($providerQuery)
        ;

        $query = new \Elastica\Query($boolQuery);
        $query->addSort(['value' => 'desc']);

        $results = $this->finder->find($query, 5000);

        return $this->fireworksModelFactory->convert($results);
    }

    protected function createDateTimeQuery(): BoolQuery
    {
        $currentYear = (new \DateTime())->format('Y');
        $years = range($currentYear - 4, $currentYear + 1);

        $dateTimeQuery = new BoolQuery();

        foreach ($years as $year) {
            $fromDateTime = new \DateTimeImmutable(sprintf('%d-12-31 12:00:00', $year));
            $untilDateTime = $fromDateTime->add(new \DateInterval('P1D'));

            $rangeQuery = new \Elastica\Query\Range('dateTime', [
                'gt' => $fromDateTime->format('Y-m-d H:i:s'),
                'lte' => $untilDateTime->format('Y-m-d H:i:s'),
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ]);

            $dateTimeQuery->addShould($rangeQuery);
        }

        return $dateTimeQuery;
    }
}
