<?php declare(strict_types=1);

namespace App\Pollution\PollutionDataFactory;

use App\Air\ViewModel\MeasurementViewModel;
use App\Entity\Data;
use App\Pollution\UniqueStrategy\Hasher;

class PollutionDataFactory extends AbstractPollutionDataFactory
{
    public function createDecoratedPollutantList(\DateTime $dateTime = null, \DateInterval $dateInterval = null, int $workingSetSize = 20): array
    {
        if (!$dateTime) {
            $dateTime = new \DateTime();
        }

        if (!$dateInterval) {
            $dateInterval = new \DateInterval('PT12H');
        }

        $dateTime->sub($dateInterval);

        $dataList = $this->getDataListFromStationList($dateTime, $dateInterval, $workingSetSize);

        $measurementViewModelList = $this->getMeasurementViewModelListFromDataList($dataList);

        $measurementViewModelList = $this->decoratePollutantList($measurementViewModelList);

        return $measurementViewModelList;
    }

    protected function getDataListFromStationList(\DateTime $fromDateTime = null, \DateInterval $interval = null, int $workingSetSize): array
    {
        $this->dataList->reset();

        $missingPollutants = $this->strategy->getMissingPollutants($this->dataList);

        foreach ($missingPollutants as $pollutantId) {
            $dataList = $this->dataRetriever->retrieveDataForCoord($this->coord, $pollutantId, $fromDateTime, $interval, 20.0, $workingSetSize);

            if (0 === count($dataList)) {
                continue;
            }

            while (!$this->strategy->isSatisfied($this->dataList, $pollutantId) && count($dataList)) {
                $data = array_shift($dataList);

                if ($this->strategy->accepts($this->dataList, $data)) {
                    $this->strategy->addDataToList($this->dataList, $data);
                }
            }
        }

        return $this->dataList->getList();
    }

    protected function getMeasurementViewModelListFromDataList(array $dataList): array
    {
        $measurementViewModelList = [];

        /** @var array $data */
        foreach ($dataList as $data) {
            /** @var Data $dataElement */
            foreach ($data as $dataElement) {
                if ($dataElement) {
                    $measurementViewModelList[$dataElement->getPollutant()][Hasher::hashData($dataElement)] = new MeasurementViewModel($dataElement);
                }
            }
        }

        return $measurementViewModelList;
    }

    protected function decoratePollutantList(array $pollutantList): array
    {
        return $this
            ->reset()
            ->measurementViewModelFactory
            ->setCoord($this->coord)
            ->setPollutantList($pollutantList)
            ->decorate()
            ->getPollutantList()
        ;
    }
}
