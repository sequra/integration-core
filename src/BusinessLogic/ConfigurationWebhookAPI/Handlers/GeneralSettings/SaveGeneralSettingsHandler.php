<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings\SaveGeneralSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings\SaveSellingCountriesRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings\SaveGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;

/**
 * Class SaveGeneralSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings
 */
class SaveGeneralSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var GeneralSettingsService $generalSettingsService
     */
    protected $generalSettingsService;

    /**
     * @var CountryConfigurationService $countryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param CountryConfigurationService $countryConfigurationService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        CountryConfigurationService $countryConfigurationService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->countryConfigurationService = $countryConfigurationService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     *
     * @throws EmptyCountryConfigurationParameterException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function handle(array $payload): Response
    {
        $generalSettingsRequest = SaveGeneralSettingsRequest::fromPayload($payload);
        $this->generalSettingsService->saveGeneralSettings($generalSettingsRequest->transformToDomainModel());

        $sellingCountriesRequest = SaveSellingCountriesRequest::fromPayload($payload);
        $this->countryConfigurationService
            ->saveCountryConfigurationForCountriesCodes($sellingCountriesRequest->getSellingCountries());

        return new SaveGeneralSettingsResponse();
    }
}
