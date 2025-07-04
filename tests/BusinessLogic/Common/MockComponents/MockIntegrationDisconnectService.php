<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;

/**
 * Class MockIntegrationDisconnectService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockIntegrationDisconnectService implements DisconnectServiceInterface
{
    /**
     * @inheritDoc
     */
    public function disconnect(): void
    {
    }
}
