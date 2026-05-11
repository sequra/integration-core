<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\BannerSettings\SaveBannerSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings\BannerSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\Exceptions\BannerImageRequiredException;
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
     *
     * @throws BannerImageRequiredException
     * @throws InvalidURLException
     */
    public function handle(array $payload): Response
    {
        $request = SaveBannerSettingsRequest::fromPayload($payload);
        $saved = $this->bannerSettingsService->setBannerSettings($request->transformToDomainModel());

        return new BannerSettingsResponse($saved);
    }
}
