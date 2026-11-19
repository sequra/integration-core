<?php

namespace SeQura\Core\BusinessLogic\Domain\Version\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;
use SeQura\Core\BusinessLogic\Domain\Version\Exceptions\FailedToRetrieveVersionException;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class VersionService
 *
 * @package SeQura\Core\BusinessLogic\Domain\Version\Services
 */
class VersionService
{
    /**
     * @var VersionServiceInterface
     */
    protected $integrationVersionService;

    public function __construct(VersionServiceInterface $integrationVersionService)
    {
        $this->integrationVersionService = $integrationVersionService;
    }

    /**
     * Returns plugin version information.
     *
     * @return Version
     *
     * @throws FailedToRetrieveVersionException
     */
    public function getVersion(): Version
    {
        try {
            return $this->integrationVersionService->getVersion();
        } catch (Exception $e) {
            throw new FailedToRetrieveVersionException(new TranslatableLabel('Failed to retrieve version.', 'general.errors.version.failed'));
        }
    }
}
