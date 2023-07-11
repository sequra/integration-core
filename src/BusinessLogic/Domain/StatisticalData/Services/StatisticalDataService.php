<?php

namespace SeQura\Core\BusinessLogic\Domain\StatisticalData\Services;

use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;

/**
 * Class StatisticalDataService
 *
 * @package SeQura\Core\BusinessLogic\Domain\StatisticalData\Services
 */
class StatisticalDataService
{
    /**
     * @var StatisticalDataRepositoryInterface
     */
    private $statisticalDataRepository;

    /**
     * @param StatisticalDataRepositoryInterface $statisticalDataRepository
     */
    public function __construct(StatisticalDataRepositoryInterface $statisticalDataRepository)
    {
        $this->statisticalDataRepository = $statisticalDataRepository;
    }

    /**
     * Retrieves statistical data from the database via statistical data repository.
     *
     * @return StatisticalData|null
     */
    public function getStatisticalData(): ?StatisticalData
    {
        return $this->statisticalDataRepository->getStatisticalData();
    }

    /**
     * Calls the repository to save statistical data to the database.
     *
     * @param StatisticalData $statisticalData
     *
     * @return void
     */
    public function saveStatisticalData(StatisticalData $statisticalData): void
    {
        $this->statisticalDataRepository->setStatisticalData($statisticalData);
    }
}
