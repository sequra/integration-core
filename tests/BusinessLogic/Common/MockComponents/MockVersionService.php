<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class MockVersionService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockVersionService implements VersionServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getVersion(): ?Version
    {
        return new Version('v1.0.1', 'v1.0.3', 'test');
    }
}
