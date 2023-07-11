<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Version;

use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Interface VersionServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Version
 */
interface VersionServiceInterface
{
    /**
     * Returns plugin version information.
     *
     * @return Version|null
     */
    public function getVersion(): ?Version;
}
