<?php

namespace SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts\AdvancedSettingsRepositoryInterface;

/**
 * Class AdvancedSettingsService.
 *
 * @package SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services
 */
class AdvancedSettingsService
{
    /**
     * @var AdvancedSettingsRepositoryInterface $advancedSettingsRepository
     */
    private $advancedSettingsRepository;

    /**
     * @param AdvancedSettingsRepositoryInterface $advancedSettingsRepository
     */
    public function __construct(AdvancedSettingsRepositoryInterface $advancedSettingsRepository)
    {
        $this->advancedSettingsRepository = $advancedSettingsRepository;
    }

    /**
     * @return ?AdvancedSettings
     */
    public function getAdvancedSettings(): ?AdvancedSettings
    {
        return $this->advancedSettingsRepository->getAdvancedSettings();
    }

    /**
     * @param AdvancedSettings $advancedSettings
     *
     * @return void
     */
    public function setAdvancedSettings(AdvancedSettings $advancedSettings): void
    {
        $this->advancedSettingsRepository->setAdvancedSettings($advancedSettings);
    }
}
