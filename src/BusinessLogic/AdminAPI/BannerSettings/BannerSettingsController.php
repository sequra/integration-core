<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\BannerSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageTooLargeException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\EmptyBannerParameterException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidBannerUrlException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;
use Throwable;

/**
 * Class BannerSettingsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\BannerSettings
 */
class BannerSettingsController
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
     * Gets active banner settings.
     *
     * @throws FailedToRetrieveSellingCountriesException
     * @throws Exception
     */
    public function getBannerSettings(): BannerSettingsResponse
    {
        return new BannerSettingsResponse(
            $this->bannerSettingsService->getBannerSettings() ?? new BannerSettings([]),
            $this->bannerService->getBannerDisplayLocations(),
            $this->countryConfigurationService->getCountryCodes()
        );
    }

    /**
     * Sets banner settings.
     *
     * @param BannerSettingsRequest $settingsRequest
     *
     * @return BannerSettingsResponse
     *
     * @throws BannerImageRequiredException
     * @throws BannerImageTooLargeException
     * @throws EmptyBannerParameterException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws InvalidBannerUrlException
     * @throws Throwable
     */
    public function setBannerSettings(BannerSettingsRequest $settingsRequest): BannerSettingsResponse
    {
        return new BannerSettingsResponse(
            $this->bannerSettingsService->setBannerSettings($settingsRequest->transformToDomainModel()),
            $this->bannerService->getBannerDisplayLocations(),
            $this->countryConfigurationService->getCountryCodes()
        );
    }
}
