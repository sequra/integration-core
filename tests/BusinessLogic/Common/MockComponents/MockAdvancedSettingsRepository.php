<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts\AdvancedSettingsRepositoryInterface;

/**
 * Class MockAdvancedSettingsRepository.
 *
 * @package Common\MockComponents
 */
class MockAdvancedSettingsRepository implements AdvancedSettingsRepositoryInterface
{
    /**
     * @var ?AdvancedSettings $settings
     */
    private $settings;

    /**
     * @inheritDoc
     */
    public function getAdvancedSettings(): ?AdvancedSettings
    {
        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function setAdvancedSettings(AdvancedSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function deleteAdvancedSettings(): void
    {
        $this->settings = null;
    }
}
