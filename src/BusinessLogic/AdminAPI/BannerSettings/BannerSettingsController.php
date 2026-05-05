<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\BannerSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Requests\BannerSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses\InvalidURLResponse;
use SeQura\Core\BusinessLogic\AdminAPI\BannerSettings\Responses\SuccessfulBannerResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;

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
     * @param BannerSettingsService $bannerSettingsService
     */
    public function __construct(BannerSettingsService $bannerSettingsService)
    {
        $this->bannerSettingsService = $bannerSettingsService;
    }

    /**
     * Gets active banner settings.
     *
     * @throws Exception
     */
    public function getBannerSettings(): BannerSettingsResponse
    {
        return new BannerSettingsResponse($this->bannerSettingsService->getBannerSettings());
    }

    /**
     * Sets banner settings.
     *
     * @param BannerSettingsRequest $settingsRequest
     *
     * @return Response
     *
     * @throws Exception
     */
    public function setBannerSettings(BannerSettingsRequest $settingsRequest): Response
    {
        try {
            $this->bannerSettingsService->setBannerSettings($settingsRequest->transformToDomainModel());
        } catch (InvalidURLException $e) {
            return new InvalidURLResponse($e->getMessage());
        }

        return new SuccessfulBannerResponse();
    }
}
