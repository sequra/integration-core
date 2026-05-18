<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings\SaveBannerSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;

/**
 * Class SaveBannerSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings
 */
class SaveBannerSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var BannerSettingsService
     */
    protected $bannerSettingsService;

    /**
     * @var BannerServiceInterface
     */
    protected $bannerService;

    /**
     * @var CountryConfigurationService
     */
    protected $countryConfigurationService;

    /**
     * @param BannerSettingsService $bannerSettingsService
     * @param BannerServiceInterface $bannerService
     * @param CountryConfigurationService $countryConfigurationService
     */
    public function __construct(
        BannerSettingsService $bannerSettingsService,
        BannerServiceInterface $bannerService,
        CountryConfigurationService $countryConfigurationService
    ) {
        $this->bannerSettingsService = $bannerSettingsService;
        $this->bannerService = $bannerService;
        $this->countryConfigurationService = $countryConfigurationService;
    }

    /**
     * @inheritDoc
     *
     * @throws BannerImageRequiredException
     * @throws InvalidURLException
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function handle(array $payload): Response
    {
        $request = SaveBannerSettingsRequest::fromPayload($payload);
        $saved = $this->bannerSettingsService->setBannerSettings($request->transformToDomainModel());

        $countryConfigurations = $this->countryConfigurationService->getCountryConfiguration() ?? [];
        $sellingCountries = array_map(function (CountryConfiguration $countryConfiguration) {
            return $countryConfiguration->getCountryCode();
        }, $countryConfigurations);

        return new BannerSettingsResponse(
            $saved,
            $this->bannerService->getBannerDisplayLocations(),
            $sellingCountries
        );
    }
}
