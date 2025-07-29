<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;

/**
 * Class MockStatisticalDataRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockStatisticalDataRepository implements StatisticalDataRepositoryInterface
{
    /**
     * @var ?StatisticalData
     */
    private $statisticalData;

    /**
     * @inheritDoc
     */
    public function getStatisticalData(): ?StatisticalData
    {
        return $this->statisticalData;
    }

    /**
     * @inheritDoc
     */
    public function setStatisticalData(StatisticalData $statisticalData): void
    {
        $this->statisticalData = $statisticalData;
    }

    /**
     * @inheritDoc
     */
    public function deleteStatisticalData(): void
    {
        $this->statisticalData = null;
    }
}
