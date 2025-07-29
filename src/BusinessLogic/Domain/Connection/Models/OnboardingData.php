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
     * @var ConnectionData[]
     */
    protected $connectionData;

    /**
     * @var bool
     */
    protected $sendStatisticalData;

    /**
     * @param ConnectionData[] $connections
     * @param bool $sendStatisticalData
     */
    public function __construct(array $connections, bool $sendStatisticalData)
    {
        $this->connectionData = $connections;
        $this->sendStatisticalData = $sendStatisticalData;
    }

    /**
     * @return ConnectionData[]
     */
    public function getConnections(): array
    {
        return $this->connectionData;
    }

    /**
     * @param ConnectionData[] $connections
     *
     * @return void
     */
    public function setConnections(array $connections): void
    {
        $this->connectionData = $connections;
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
