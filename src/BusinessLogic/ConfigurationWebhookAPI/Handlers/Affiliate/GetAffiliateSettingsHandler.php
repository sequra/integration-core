<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Affiliate;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate\AffiliateSettingsResponse;
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
        // GET is a boolean-state read: return only whether the feature is enabled, never echo the
        // offer id or security token. The service always yields settings (disabled by default when
        // none are stored), so the response is enabled=false when nothing is configured.
        return new AffiliateSettingsResponse($this->affiliateSettingsService->getAffiliateSettings()->isEnabled());
    }
}
