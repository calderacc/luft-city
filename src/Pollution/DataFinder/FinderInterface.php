<?php declare(strict_types=1);

namespace App\Pollution\DataFinder;

use Elastica\Query;

interface FinderInterface
{
    public function find(Query $query, int $limit = null, array $options = []): array;
}
