<?php

namespace SeQura\Core\BusinessLogic\Domain\Disconnect\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;

/**
 * Class DisconnectService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Disconnect\Services
 */
class DisconnectService
{
    /**
     * @var DisconnectServiceInterface
     */
    private $integrationDisconnectService;

    /**
     * @param DisconnectServiceInterface $integrationDisconnectService
     */
    public function __construct(DisconnectServiceInterface $integrationDisconnectService)
    {
        $this->integrationDisconnectService = $integrationDisconnectService;
    }

    /**
     * Disconnects integration from store.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->integrationDisconnectService->disconnect();
    }
}
