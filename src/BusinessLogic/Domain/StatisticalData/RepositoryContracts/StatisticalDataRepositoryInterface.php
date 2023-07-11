<?php

namespace SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;

/**
 * Interface StatisticalDataRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts
 */
interface StatisticalDataRepositoryInterface
{
    /**
     * Returns statistical data for current store context.
     *
     * @return StatisticalData|null
     */
    public function getStatisticalData(): ?StatisticalData;

    /**
     * Insert/update statistical data for current store context.
     *
     * @param StatisticalData $statisticalData
     *
     * @return void
     */
    public function setStatisticalData(StatisticalData $statisticalData): void;
}
