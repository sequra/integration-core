<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;

/**
 * Class MockGeneralSettingsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockGeneralSettingsRepository implements GeneralSettingsRepositoryInterface
{
    /**
     * @var ?GeneralSettings
     */
    private $generalSettings;

    /**
     * @inheritDoc
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * @inheritDoc
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void
    {
        $this->generalSettings = $generalSettings;
    }

    /**
     * @inheritDoc
     */
    public function deleteGeneralSettings(): void
    {
        $this->generalSettings = null;
    }
}
