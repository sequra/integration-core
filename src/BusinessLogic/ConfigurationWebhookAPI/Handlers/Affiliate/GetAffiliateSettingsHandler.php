<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Affiliate;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate\AffiliateSettingsResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateSettingsService;

/**
 * Class GetAffiliateSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Affiliate
 */
class GetAffiliateSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var AffiliateSettingsService
     */
    protected $affiliateSettingsService;

    /**
     * @param AffiliateSettingsService $affiliateSettingsService
     */
    public function __construct(AffiliateSettingsService $affiliateSettingsService)
    {
        $this->affiliateSettingsService = $affiliateSettingsService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handle(array $payload): Response
    {
        $affiliateSettings = $this->affiliateSettingsService->getAffiliateSettings();

        if (!$affiliateSettings) {
            return new SuccessResponse();
        }

        return new AffiliateSettingsResponse($affiliateSettings);
    }
}
