<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;

/**
 * Class GetBannerSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings
 */
class GetBannerSettingsHandler implements TopicHandlerInterface
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
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handle(array $payload): Response
    {
        $bannerSettings = $this->bannerSettingsService->getBannerSettings();

        if (!$bannerSettings) {
            return new SuccessResponse();
        }

        return new BannerSettingsResponse($bannerSettings);
    }
}
