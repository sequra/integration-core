<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings\SaveGeneralSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings\SaveSellingCountriesRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\GeneralSettings\SaveStatisticalDataRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\GeneralSettings\SaveGeneralSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;

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
     * @var StatisticalDataService $statisticalDataService
     */
    protected $statisticalDataService;

    /**
     * @param GeneralSettingsService $generalSettingsService
     * @param CountryConfigurationService $countryConfigurationService
     * @param StatisticalDataService $statisticalDataService
     */
    public function __construct(
        GeneralSettingsService $generalSettingsService,
        CountryConfigurationService $countryConfigurationService,
        StatisticalDataService $statisticalDataService
    ) {
        $this->generalSettingsService = $generalSettingsService;
        $this->countryConfigurationService = $countryConfigurationService;
        $this->statisticalDataService = $statisticalDataService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     *
     * @throws EmptyCountryConfigurationParameterException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws \Exception
     */
    public function handle(array $payload): Response
    {
        $generalSettingsRequest = SaveGeneralSettingsRequest::fromPayload($payload);
        $this->generalSettingsService->saveGeneralSettings($generalSettingsRequest->transformToDomainModel());

        $sellingCountriesRequest = SaveSellingCountriesRequest::fromPayload($payload);
        $this->countryConfigurationService
            ->saveCountryConfigurationForCountriesCodes($sellingCountriesRequest->getSellingCountries());

        $this->saveStatisticalData(SaveStatisticalDataRequest::fromPayload($payload)->getSendStatisticalData());

        return new SaveGeneralSettingsResponse();
    }

    /**
     * Stores whether SeQura may collect statistical data, when the payload carried the field.
     *
     * @param bool|null $sendStatisticalData
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function saveStatisticalData(?bool $sendStatisticalData): void
    {
        if ($sendStatisticalData === null) {
            return;
        }

        $statisticalData = $this->statisticalDataService->getStatisticalData();

        if ($statisticalData !== null && $statisticalData->isSendStatisticalData() === $sendStatisticalData) {
            return;
        }

        $this->statisticalDataService->saveStatisticalData(new StatisticalData($sendStatisticalData));
    }
}
