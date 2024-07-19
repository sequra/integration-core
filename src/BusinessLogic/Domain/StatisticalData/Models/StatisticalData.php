<?php

namespace SeQura\Core\BusinessLogic\Domain\StatisticalData\Models;

/**
 * Class StatisticalData
 *
 * @package SeQura\Core\BusinessLogic\Domain\StatisticalData\Models
 */
class StatisticalData
{
    /**
     * @var bool
     */
    protected $sendStatisticalData;

    /**
     * @param bool $sendStatisticalData
     */
    public function __construct(bool $sendStatisticalData)
    {
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * @return bool
     */
    public function isSendStatisticalData(): bool
    {
        return $this->sendStatisticalData;
    }

    /**
     * @param bool $sendStatisticalData
     */
    public function setSendStatisticalData(bool $sendStatisticalData): void
    {
        $this->sendStatisticalData = $sendStatisticalData;
    }
}
