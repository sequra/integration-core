<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;

/**
 * Class GeneralSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services
 */
class GeneralSettingsService
{
    /**
     * @var GeneralSettingsRepositoryInterface
     */
    protected $generalSettingsRepository;

    /**
     * @param GeneralSettingsRepositoryInterface $generalSettingsRepository
     */
    public function __construct(GeneralSettingsRepositoryInterface $generalSettingsRepository)
    {
        $this->generalSettingsRepository = $generalSettingsRepository;
    }

    /**
     * Retrieves general settings from the database via general settings repository.
     *
     * @return GeneralSettings|null
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        return $this->generalSettingsRepository->getGeneralSettings();
    }

    /**
     * Calls the repository to save the general settings to the database.
     *
     * @param GeneralSettings $generalSettings
     *
     * @return void
     */
    public function saveGeneralSettings(GeneralSettings $generalSettings): void
    {
        $this->generalSettingsRepository->setGeneralSettings($generalSettings);
    }
}
