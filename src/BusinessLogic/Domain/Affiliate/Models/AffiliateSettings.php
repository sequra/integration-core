<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\Models;

/**
 * Class AffiliateSettings.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\Models
 */
class AffiliateSettings
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
        // "Enabled" requires credentials: an enabled flag with no offer id or security token is not
        // usable, so it is coerced to disabled. This keeps isEnabled() trustworthy for every consumer
        // (postback gating, config provider) without each one having to re-check the credentials.
        $this->isEnabled = $isEnabled && '' !== $offerId && '' !== $securityToken;
        $this->offerId = $offerId;
        $this->securityToken = $securityToken;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string
     */
    public function getOfferId(): string
    {
        return $this->offerId;
    }

    /**
     * @return string
     */
    public function getSecurityToken(): string
    {
        return $this->securityToken;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'isEnabled' => $this->isEnabled,
            'offerId' => $this->offerId,
            'securityToken' => $this->securityToken,
        ];
    }
}
