<?php declare(strict_types=1);

namespace App\Pollution\DataFinder;

use Elastica\Query;
use FOS\ElasticaBundle\Finder\FinderInterface as FosFinderInterface;

class ElasticOrmFinder implements FinderInterface
{
    protected FosFinderInterface $fosFinder;

    public function __construct(FosFinderInterface $fosFinder)
    {
        $this->fosFinder = $fosFinder;
    }

    public function find(Query $query, int $limit = null, array $options = []): array
    {
        return $this->fosFinder->find($query, $limit, $options);
    }
}
