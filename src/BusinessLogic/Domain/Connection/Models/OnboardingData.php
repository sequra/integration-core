<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\Models;

/**
 * Class OnboardingData
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\Models
 */
class OnboardingData
{
    /**
     * @var ConnectionData
     */
    protected $connectionData;

    /**
     * @var bool
     */
    protected $sendStatisticalData;

    /**
     * @param ConnectionData $connectionData
     * @param bool $sendStatisticalData
     */
    public function __construct(ConnectionData $connectionData, bool $sendStatisticalData)
    {
        $this->connectionData = $connectionData;
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @param ConnectionData $connectionData
     */
    public function setConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionData = $connectionData;
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
