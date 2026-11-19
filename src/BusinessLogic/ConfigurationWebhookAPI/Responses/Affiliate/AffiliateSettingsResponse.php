<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class AffiliateSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\Affiliate
 */
class AffiliateSettingsResponse extends Response
{
    /**
     * @var bool $isEnabled
     */
    protected $isEnabled;

    /**
     * @param bool $isEnabled
     */
    public function __construct(bool $isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['isEnabled' => $this->isEnabled];
    }
}
