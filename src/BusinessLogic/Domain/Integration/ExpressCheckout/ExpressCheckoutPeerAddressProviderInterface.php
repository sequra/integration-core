<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutPeerAddressProviderInterface
{
    /**
     * @return string|null The connection's own peer address, never a forwarded header; behind a reverse proxy or CDN every shopper shares the proxy's address, so the per-address budget is per proxy address.
     */
    public function getPeerAddress(): ?string;
}
