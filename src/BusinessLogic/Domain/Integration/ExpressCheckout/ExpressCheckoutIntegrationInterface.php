<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutPage;

/**
 * Interface ExpressCheckoutIntegrationInterface
 *
 * Platform-supplied contract that lists the storefront pages where the
 * integration is capable of hosting Express Checkout buttons.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutIntegrationInterface
{
    /**
     * Returns the pages supported by the platform integration.
     *
     * @return ExpressCheckoutPage[]
     */
    public function getAvailablePages(): array;
}
