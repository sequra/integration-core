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
     * @param ConnectionData[] $connections
     */
    public function __construct(array $connections)
    {
        $this->connectionData = $connections;
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
}
