<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;

/**
 * Class UIStateService
 *
 * @package SeQura\Core\BusinessLogic\Domain\UIState\Services
 */
class UIStateService
{
    /**
     * @var ConnectionDataRepositoryInterface
     */
    protected $connectionDataRepository;

    /**
     * @var CountryConfigurationRepositoryInterface
     */
    protected $countryConfigurationRepository;

    /**
     * @var WidgetSettingsRepositoryInterface
     */
    protected $widgetSettingsRepository;

    /**
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     */
    public function __construct(
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        WidgetSettingsRepositoryInterface $widgetSettingsRepository
    )
    {
        $this->connectionDataRepository = $connectionDataRepository;
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->widgetSettingsRepository = $widgetSettingsRepository;
    }

    /**
     * Returns true it the application state is onboarding.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isOnboardingState(): bool
    {
        $connectionData = $this->connectionDataRepository->getConnectionData();
        $countryConfiguration = $this->countryConfigurationRepository->getCountryConfiguration();
        $widgetSettings = $this->widgetSettingsRepository->getWidgetSettings();

        return !($connectionData && $countryConfiguration && $widgetSettings);
    }
}
