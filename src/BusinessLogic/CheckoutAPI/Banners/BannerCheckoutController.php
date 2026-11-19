<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Banners;

use SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Requests\GetBannerForLocationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Banners\Responses\GetBannerForLocationResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;

/**
 * Class BannerCheckoutController
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Banners
 */
class BannerCheckoutController
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
     * Returns banner data
     *
     * @param GetBannerForLocationRequest $request
     *
     * @return GetBannerForLocationResponse
     */
    public function getBannerForLocation(GetBannerForLocationRequest $request): GetBannerForLocationResponse
    {
        return new GetBannerForLocationResponse(
            $this->bannerSettingsService->getBannerData($request->getCountry(), $request->getDisplayLocation())
        );
    }
}
