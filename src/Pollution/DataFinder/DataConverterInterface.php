<?php declare(strict_types=1);

namespace App\Pollution\DataFinder;

use App\Entity\Data;
use Elastica\Result;

interface DataConverterInterface
{
    public function convert(Result $result): ?Data;
    public function convertArray(array $elasticResult): ?Data;
}