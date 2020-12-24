<?php declare(strict_types=1);

namespace App\Producer\Value;

use App\Pollution\DataPersister\ElasticPersister;
use App\Pollution\Value\Value;

class ElasticValueProducer implements ValueProducerInterface
{
    protected ElasticPersister $persister;

    public function __construct(ElasticPersister $persister)
    {
        $this->persister = $persister;
    }

    /** @deprecated  */
    public function publish(Value $value): ValueProducerInterface
    {
        $this->persister->persistValues([$value]);
        
        return $this;
    }

    public function publishValue(Value $value): ValueProducerInterface
    {
        $this->persister->persistValues([$value]);

        return $this;
    }

    public function publishValues(array $valueList): ValueProducerInterface
    {
        $this->persister->persistValues($valueList);

        return $this;
    }
}
