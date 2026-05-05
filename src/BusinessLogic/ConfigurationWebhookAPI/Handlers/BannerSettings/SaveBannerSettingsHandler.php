<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings\SaveBannerSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings\InvalidURLResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\WidgetSettings\SaveWidgetSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\InvalidURLException;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Services\BannerSettingsService;

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
     * @param BannerSettingsService $bannerSettingsService
     */
    public function __construct(BannerSettingsService $bannerSettingsService)
    {
        $this->bannerSettingsService = $bannerSettingsService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $request = SaveBannerSettingsRequest::fromPayload($payload);
        try {
            $this->bannerSettingsService->setBannerSettings($request->transformToDomainModel());
        } catch (InvalidURLException $e) {
            return new InvalidURLResponse($e->getMessage());
        }

        return new SaveWidgetSettingsResponse();
    }
}
