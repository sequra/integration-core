<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;

/**
 * Class StatisticalDataResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses
 */
class StatisticalDataResponse extends Response
{
    /**
     * @var StatisticalData|null
     */
    protected $statisticalData;

    /**
     * @param StatisticalData|null $statisticalData
     */
    public function __construct(?StatisticalData $statisticalData)
    {
        $this->statisticalData = $statisticalData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->statisticalData) {
            return [];
        }

        return ['sendStatisticalData' => $this->statisticalData->isSendStatisticalData()];
    }
}
