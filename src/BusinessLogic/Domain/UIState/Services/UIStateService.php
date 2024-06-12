<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
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
     * @var ConnectionService
     */
    protected $connectionService;

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
     * @param ConnectionService $connectionService
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     */
    public function __construct(
        ConnectionService $connectionService,
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CountryConfigurationRepositoryInterface $countryConfigurationRepository,
        WidgetSettingsRepositoryInterface $widgetSettingsRepository
    ) {
        $this->connectionService = $connectionService;
        $this->connectionDataRepository = $connectionDataRepository;
        $this->countryConfigurationRepository = $countryConfigurationRepository;
        $this->widgetSettingsRepository = $widgetSettingsRepository;
    }

    /**
     * Returns true it the application state is onboarding.
     *
     * @param bool $useWidgets
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isOnboardingState(bool $useWidgets): bool
    {
        $connectionData = $this->connectionDataRepository->getConnectionData();
        $countryConfiguration = $this->countryConfigurationRepository->getCountryConfiguration();

        if ($useWidgets) {
            $widgetSettings = $this->widgetSettingsRepository->getWidgetSettings();
            if (!$widgetSettings) {
                return true;
            }
        }

        if ($connectionData && $countryConfiguration) {
            $connectionData->setMerchantId($countryConfiguration[0]->getMerchantId());
            $this->connectionService->isConnectionDataValid($connectionData);

            return false;
        }

        return true;
    }
}
