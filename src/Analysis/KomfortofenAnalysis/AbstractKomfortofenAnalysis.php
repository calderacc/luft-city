<?php declare(strict_types=1);

namespace App\Analysis\KomfortofenAnalysis;

use App\Pollution\DataFinder\FinderInterface;
use App\Pollution\Pollutant\PollutantInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;

abstract class AbstractKomfortofenAnalysis implements KomfortofenAnalysisInterface
{
    protected float $minSlope = 1.0;

    protected float $maxSlope = 300.0;

    /** @var PollutantInterface $pollutant */
    protected $pollutant;

    protected FinderInterface $finder;

    /** @var \DateTimeInterface $fromDateTime */
    protected $fromDateTime;

    /** @var \DateTimeInterface $untilDateTime */
    protected $untilDateTime;

    protected KomfortofenModelFactoryInterface $komfortofenModelFactory;

    public function __construct(FinderInterface $finder, KomfortofenModelFactoryInterface $komfortofenModelFactory)
    {
        $this->finder = $finder;
        $this->komfortofenModelFactory = $komfortofenModelFactory;
    }

    public function setMinSlope(float $minSlope): KomfortofenAnalysisInterface
    {
        $this->minSlope = $minSlope;

        return $this;
    }

    public function setPollutant(PollutantInterface $pollutant): KomfortofenAnalysisInterface
    {
        $this->pollutant = $pollutant;

        return $this;
    }

    public function setFromDateTime(\DateTimeInterface $fromDateTime): KomfortofenAnalysisInterface
    {
        $this->fromDateTime = $fromDateTime;

        return $this;
    }

    public function setUntilDateTime(\DateTimeInterface $untilDateTime): KomfortofenAnalysisInterface
    {
        $this->untilDateTime = $untilDateTime;

        return $this;
    }

    public function setMaxSlope(float $maxSlope): KomfortofenAnalysisInterface
    {
        $this->maxSlope = $maxSlope;

        return $this;
    }
}
