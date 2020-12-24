<?php declare(strict_types=1);

namespace App\Analysis\KomfortofenAnalysis;

use App\Air\Measurement\MeasurementInterface;

class KomfortofenAnalysis extends AbstractKomfortofenAnalysis
{
    public function analyze(): array
    {
        $pollutantQuery = new \Elastica\Query\Term(['pollutant' => MeasurementInterface::MEASUREMENT_PM10]);

        $dateTimeQuery = new \Elastica\Query\Range('dateTime', [
            'gt' => $this->fromDateTime->format('Y-m-d H:i:s'),
            'lte' => $this->untilDateTime->format('Y-m-d H:i:s'),
            'format' => 'yyyy-MM-dd HH:mm:ss'
        ]);

        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolQuery
            ->addMust($pollutantQuery)
            ->addMust($dateTimeQuery);

        $histogram = new \Elastica\Aggregation\DateHistogram('histogram_agg', 'dateTime', 'hour');
        $histogram->setTimezone('Europe/Berlin');
        $histogram->setFormat('yyyy-MM-dd HH:mm:ss');

        $termAgg = new \Elastica\Aggregation\Terms('station_agg');
        $termAgg->setField('station');
        $termAgg->addAggregation($histogram);

        $max = new \Elastica\Aggregation\Max('max_agg');
        $max->setField('value');
        $histogram->addAggregation($max);

        $derive = new \Elastica\Aggregation\Derivative('derivative_agg');
        $derive->setBucketsPath('max_agg');
        $histogram->addAggregation($derive);

        $bucketSelector = new \Elastica\Aggregation\BucketSelector('bucket_selector');
        $bucketSelector->setBucketsPath(['derivative' => 'derivative_agg']);
        $bucketSelector->setGapPolicy('skip');
        $bucketSelector->setScript(sprintf('params.derivative != null && params.derivative > %f && params.derivative < %f', $this->minSlope, $this->maxSlope));
        $histogram->addAggregation($bucketSelector);

        $topHistsAgg = new \Elastica\Aggregation\TopHits('top_hits_agg');
        $topHistsAgg->setSize(1);
        $topHistsAgg->setSort(['value' => ['order' => 'desc']]);
        $histogram->addAggregation($topHistsAgg);

        $query = new \Elastica\Query($boolQuery);
        $query->addAggregation($histogram);

        $buckets = $this->finder->findAggregations($query);

        return $this->komfortofenModelFactory->convert($buckets['histogram_agg']['buckets']);
    }
}
