<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Disconnect;

/**
 * Interface DisconnectServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Disconnect
 */
interface DisconnectServiceInterface
{
    /**
     * Disconnect integration from store.
     *
     * @return void
     */
    public function disconnect(): void;
}
