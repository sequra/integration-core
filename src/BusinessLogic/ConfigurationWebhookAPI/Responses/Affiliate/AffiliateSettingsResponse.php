<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;

/**
 * Class AffiliateSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate
 */
class AffiliateSettingsResponse extends Response
{
    /**
     * @var AffiliateSettings $settings
     */
    protected $settings;

    /**
     * @param ?AffiliateSettings $settings
     */
    public function __construct(?AffiliateSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return !$this->settings ? [] : $this->settings->toArray();
    }
}
