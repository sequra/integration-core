<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class MockGeneralSettingsService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockGeneralSettingsService extends GeneralSettingsService
{
    /**
     * @var GeneralSettings
     */
    private $generalSettings = null;

    /**
     * @return ?GeneralSettings
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function saveGeneralSettings(GeneralSettings $generalSettings): void
    {
        $this->generalSettings = $generalSettings;
    }
}
