<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Affiliate;

use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\ConfigurationWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;

/**
 * Class SaveAffiliateSettingsRequest.
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\Affiliate
 */
class SaveAffiliateSettingsRequest extends ConfigurationWebhookRequest
{
    /**
     * @var bool $isEnabled
     */
    private $isEnabled;

    /**
     * @var string $offerId
     */
    private $offerId;

    /**
     * @var string $securityToken
     */
    private $securityToken;

    /**
     * @param bool $isEnabled
     * @param string $offerId
     * @param string $securityToken
     */
    public function __construct(bool $isEnabled, string $offerId, string $securityToken)
    {
        $this->isEnabled = $isEnabled;
        $this->offerId = $offerId;
        $this->securityToken = $securityToken;
    }

    /**
     * @param mixed[] $payload
     *
     * @return self
     */
    public static function fromPayload(array $payload): object
    {
        return new self(
            $payload['isEnabled'] ?? false,
            (string)($payload['offerId'] ?? ''),
            (string)($payload['securityToken'] ?? '')
        );
    }

    /**
     * @return AffiliateSettings
     */
    public function transformToDomainModel(): AffiliateSettings
    {
        return new AffiliateSettings($this->isEnabled, $this->offerId, $this->securityToken);
    }
}
