<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo;

/**
 * Optional companion interface for {@see StoreInfoServiceInterface}.
 *
 * A shop platform may identify an order by more than one value — an internal id and
 * a customer-facing reference, for example — and let the merchant choose which of
 * them is sent to SeQura as the primary order reference. Such an integration
 * additionally implements this interface to publish the values it can send, so that
 * the merchant is offered them where the general settings are configured.
 *
 * Implementing this interface is OPTIONAL and fully backwards-compatible: an
 * integration that does not implement it publishes no order identifiers, and the
 * choice is simply not offered for that platform.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo
 */
interface OrderIdentifiersProviderInterface
{
    /**
     * Returns the order identifiers the integration can send to SeQura, as a map of
     * the value stored in the general settings to the label describing it to the
     * merchant.
     *
     * @return array<string, string>
     */
    public function getAvailableOrderIdentifiers(): array;
}
