<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Disconnect\Services\DisconnectService;

/**
 * Class MockDisconnectService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockDisconnectService extends DisconnectService
{
    public function disconnect(string $deploymentId, bool $isFullDisconnect): void
    {
    }
}
