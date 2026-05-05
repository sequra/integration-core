<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\BannerSettings\BannerDisplayLocationsResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\Banner\BannerServiceInterface;

/**
 * Class GetBannerDisplayLocationsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\BannerSettings
 */
class GetBannerDisplayLocationsHandler implements TopicHandlerInterface
{
    /**
     * @var BannerServiceInterface
     */
    protected $bannerService;

    /**
     * @param BannerServiceInterface $bannerService
     */
    public function __construct(BannerServiceInterface $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return new BannerDisplayLocationsResponse($this->bannerService->getBannerDisplayLocations());
    }
}
