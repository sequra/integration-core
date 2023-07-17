<?php

namespace SeQura\Core\BusinessLogic\Domain\UIState\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;

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
    private $connectionDataRepository;

    /**
     * @var CountryConfigurationRepositoryInterface
     */
    private $countryConfigurationRepository;

    /**
     * @param ConnectionDataRepositoryInterface $connectionDataRepository
     * @param CountryConfigurationRepositoryInterface $countryConfigurationRepository
     */
    public function __construct(
        ConnectionDataRepositoryInterface $connectionDataRepository,
        CountryConfigurationRepositoryInterface $countryConfigurationRepository
    )
    {
        $this->connectionDataRepository = $connectionDataRepository;
        $this->countryConfigurationRepository = $countryConfigurationRepository;
    }

    /**
     * Returns true it the application state is onboarding.
     *
     * @return bool
     */
    public function isOnboardingState(): bool
    {
        $connectionData = $this->connectionDataRepository->getConnectionData();
        $countryConfiguration = $this->countryConfigurationRepository->getCountryConfiguration();
        // TODO: Extend to handle widget configuration as well.

        return !($connectionData && $countryConfiguration);
    }
}
