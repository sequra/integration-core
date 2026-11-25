<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutSessionIdProviderInterface
{
    /**
     * @return string|null The id of the current shopper session, or null when there is none.
     */
    public function getSessionId(): ?string;
}
