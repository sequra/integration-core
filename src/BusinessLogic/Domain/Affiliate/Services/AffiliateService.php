<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\Services;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Deployments\Exceptions\DeploymentNotFoundException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class AffiliateService.
 *
 * Orchestrates the outbound affiliate postbacks. The affiliate credentials are sourced here from
 * the stored AffiliateSettings (the single source of truth, populated by the inbound config): the
 * caller supplies only the order-specific data. This keeps the credentials server-side (the plugin
 * never has to echo them back) and gates every postback on a single, trustworthy enabled flag.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\Services
 */
class AffiliateService
{
    /**
     * @var AffiliateSettingsService $affiliateSettingsService
     */
    private $affiliateSettingsService;

    /**
     * @var AffiliateProxyInterface $affiliateProxy
     */
    private $affiliateProxy;

    /**
     * @param AffiliateSettingsService $affiliateSettingsService
     * @param AffiliateProxyInterface $affiliateProxy
     */
    public function __construct(
        AffiliateSettingsService $affiliateSettingsService,
        AffiliateProxyInterface $affiliateProxy
    ) {
        $this->affiliateSettingsService = $affiliateSettingsService;
        $this->affiliateProxy = $affiliateProxy;
    }

    /**
     * Reports a conversion to the affiliate network. Returns false without sending anything when
     * affiliate marketing is not enabled for the store.
     *
     * @param string $merchantId
     * @param string $transactionId
     * @param float $amount
     * @param string $orderReference
     *
     * @return bool
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException When the merchant is not connected.
     * @throws CredentialsNotFoundException When the merchant has no stored credentials.
     * @throws DeploymentNotFoundException When the merchant's deployment cannot be resolved.
     */
    public function reportConversion(
        string $merchantId,
        string $transactionId,
        float $amount,
        string $orderReference
    ): bool {
        $settings = $this->affiliateSettingsService->getAffiliateSettings();
        if (!$settings->isEnabled()) {
            return false;
        }

        return $this->affiliateProxy->sendConversion(new AffiliateConversion(
            $merchantId,
            $settings->getOfferId(),
            $settings->getSecurityToken(),
            $transactionId,
            $amount,
            $orderReference
        ));
    }

    /**
     * Reports a cancellation to seQura's conversion-status webhook. Returns false without sending
     * anything when affiliate marketing is not enabled for the store.
     *
     * @param string $merchantId
     * @param string $transactionId
     *
     * @return bool
     *
     * @throws HttpRequestException
     * @throws ConnectionDataNotFoundException When the merchant is not connected.
     * @throws CredentialsNotFoundException When the merchant has no stored credentials.
     * @throws DeploymentNotFoundException When the merchant's deployment cannot be resolved.
     */
    public function reportCancellation(string $merchantId, string $transactionId): bool
    {
        $settings = $this->affiliateSettingsService->getAffiliateSettings();
        if (!$settings->isEnabled()) {
            return false;
        }

        return $this->affiliateProxy->sendCancellation(new AffiliateCancellation(
            $merchantId,
            $settings->getOfferId(),
            $settings->getSecurityToken(),
            $transactionId
        ));
    }
}
