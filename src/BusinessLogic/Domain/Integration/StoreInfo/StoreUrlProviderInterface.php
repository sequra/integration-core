<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo;

/**
 * Optional companion interface for {@see StoreInfoServiceInterface}.
 *
 * Webhook signature validation and HMAC computation in
 * {@see \SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService}
 * only need the storeUrl, not the full {@see \SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo}
 * payload. Integrations whose
 * {@see StoreInfoServiceInterface::getStoreInfo()} implementation is expensive
 * (for example, when populating it requires live calls to the host platform
 * to read fields the HMAC does not use) can additionally implement this
 * interface to expose a cheap, side-effect-free storeUrl lookup for HMAC
 * purposes.
 *
 * When implemented, {@see \SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService::signaturePayload()}
 * calls {@see self::getStoreUrl()} instead of building a full StoreInfo via
 * {@see StoreInfoServiceInterface::getStoreInfo()}, avoiding the cost of
 * populating the full payload on every register / delete / inbound webhook.
 *
 * Implementing this interface is OPTIONAL and fully backwards-compatible —
 * integrations that do NOT implement it continue to receive the storeUrl
 * through the existing {@see StoreInfoServiceInterface::getStoreInfo()} path.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo
 */
interface StoreUrlProviderInterface
{
    /**
     * Returns the canonical storeUrl used in the webhook signature payload.
     *
     * Must produce the exact same value as
     * {@see StoreInfoServiceInterface::getStoreInfo()}'s
     * {@see \SeQura\Core\BusinessLogic\Domain\Stores\Models\StoreInfo::getStoreUrl()}
     * would return for the current tenant context, otherwise webhook signature
     * validation will fail.
     *
     * Implementations should be cheap and side-effect-free — typically a
     * string built from the configured store domain.
     *
     * @return string
     */
    public function getStoreUrl(): string;
}
