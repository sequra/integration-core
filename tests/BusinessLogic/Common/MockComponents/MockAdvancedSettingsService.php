<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;

/**
 * Class MockAdvancedSettingsService.
 *
 * @package Common\MockComponents
 */
class MockAdvancedSettingsService extends AdvancedSettingsService
{
    /**
     * @var ?AdvancedSettings $advancedSettings
     */
    private $advancedSettings;

    /**
     * @return ?AdvancedSettings
     */
    public function getAdvancedSettings(): ?AdvancedSettings
    {
        return $this->advancedSettings;
    }

    /**
     * @param ?AdvancedSettings $advancedSettings
     *
     * @return void
     */
    public function setAdvancedSettings(?AdvancedSettings $advancedSettings): void
    {
        $this->advancedSettings = $advancedSettings;
    }
}
