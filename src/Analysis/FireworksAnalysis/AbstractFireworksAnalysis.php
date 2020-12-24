<?php declare(strict_types=1);

namespace App\Analysis\FireworksAnalysis;

use App\Pollution\DataFinder\FinderInterface;

abstract class AbstractFireworksAnalysis implements FireworksAnalysisInterface
{
    protected float $minSlope = 10.0;

    protected float $maxSlope = 5000.0;

    protected FinderInterface $finder;

    protected FireworksModelFactoryInterface $fireworksModelFactory;

    public function __construct(FinderInterface $finder, FireworksModelFactoryInterface $fireworksModelFactory)
    {
        $this->finder = $finder;
        $this->fireworksModelFactory = $fireworksModelFactory;
    }
}
