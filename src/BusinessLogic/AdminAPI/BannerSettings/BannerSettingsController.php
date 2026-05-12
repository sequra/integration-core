<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\BannerSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;

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
     * @param BannerSettingsService $bannerSettingsService
     * @param BannerServiceInterface $bannerService
     */
    public function __construct(
        BannerSettingsService $bannerSettingsService,
        BannerServiceInterface $bannerService
    ) {
        $this->bannerSettingsService = $bannerSettingsService;
        $this->bannerService = $bannerService;
    }

    /**
     * Gets active banner settings.
     *
     * @throws Exception
     */
    public function getBannerSettings(): BannerSettingsResponse
    {
        return new BannerSettingsResponse(
            $this->bannerSettingsService->getBannerSettings(),
            $this->bannerService->getBannerDisplayLocations()
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
     * @throws InvalidURLException
     */
    public function setBannerSettings(BannerSettingsRequest $settingsRequest): BannerSettingsResponse
    {
        return new BannerSettingsResponse(
            $this->bannerSettingsService->setBannerSettings($settingsRequest->transformToDomainModel()),
            $this->bannerService->getBannerDisplayLocations()
        );
    }
}
