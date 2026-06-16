<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Affiliate;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Affiliate\SaveAffiliateSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateSettingsService;

/**
 * Class SaveAffiliateSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\Affiliate
 */
class SaveAffiliateSettingsHandler implements TopicHandlerInterface
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
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $request = SaveAffiliateSettingsRequest::fromPayload($payload);
        $this->affiliateSettingsService->setAffiliateSettings($request->transformToDomainModel());

        return new SuccessResponse();
    }
}
