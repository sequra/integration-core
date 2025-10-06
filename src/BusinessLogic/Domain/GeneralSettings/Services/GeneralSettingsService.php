<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
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
     * @var ConnectionService
     */
    protected $connectionService;

    /**
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @param GeneralSettingsRepositoryInterface $generalSettingsRepository
     */
    public function __construct(
        GeneralSettingsRepositoryInterface $generalSettingsRepository,
        ConnectionService $connectionService,
        CountryConfigurationService $countryConfigurationService
    ) {
        $this->generalSettingsRepository = $generalSettingsRepository;
        $this->connectionService = $connectionService;
        $this->countryConfigurationService = $countryConfigurationService;
    }

    /**
     * Retrieves general settings from the database via general settings repository.
     *
     * @return GeneralSettings|null
     */
    public function getGeneralSettings(): ?GeneralSettings
    {
        $generalSettings = $this->generalSettingsRepository->getGeneralSettings();
        if ($generalSettings) {
            $enabledForServices = [];
            $allowFirstServicePaymentDelay = [];
            $allowServiceRegistrationItems = [];
            $countryConfigurations = $this->countryConfigurationService->getCountryConfiguration();
            if (!empty($countryConfigurations)) {
                foreach ($this->connectionService->getCredentials() as $credentials) {
                    // The merchantID must be explicity enabled in the configuration.
                    $isAvailableInCountryConfig = false;
                    foreach ($countryConfigurations as $countryConfiguration) {
                        if ($countryConfiguration->getMerchantId() === $credentials->getMerchantId()) {
                            $isAvailableInCountryConfig = true;
                            break;
                        }
                    }
                    if (!$isAvailableInCountryConfig) {
                        continue;
                    }

                    if ($credentials->isEnabledForServices()) {
                        $enabledForServices[] = $credentials->getCountry();
                    }
                    if ($credentials->isAllowFirstServicePaymentDelay()) {
                        $allowFirstServicePaymentDelay[] = $credentials->getCountry();
                    }
                    if ($credentials->isAllowServiceRegistrationItems()) {
                        $allowServiceRegistrationItems[] = $credentials->getCountry();
                    }
                }
            }
            $generalSettings->setEnabledForServices($enabledForServices);
            $generalSettings->setAllowFirstServicePaymentDelay($allowFirstServicePaymentDelay);
            $generalSettings->setAllowServiceRegistrationItems($allowServiceRegistrationItems);
        }
        return $generalSettings;
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
