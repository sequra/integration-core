<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Services;

use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
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
     * Returns the current application state.
     *
     * @return IntegrationUIStateResponse
     *
     * @throws \Exception
     */
    public function getState(): IntegrationUIStateResponse
    {
        $connectionData = $this->connectionDataRepository->getConnectionData();

        if ($connectionData === null) {
            return IntegrationUIStateResponse::connection();
        }

        $countryConfiguration = $this->countryConfigurationRepository->getCountryConfiguration();
        if ($countryConfiguration === null) {
            return IntegrationUIStateResponse::countryConfiguration();
        }

        $widgetSettings = $this->widgetSettingsRepository->getWidgetSettings();
        if ($widgetSettings === null) {
            return IntegrationUIStateResponse::widgetConfiguration();
        }

        return IntegrationUIStateResponse::dashboard();
    }
}
