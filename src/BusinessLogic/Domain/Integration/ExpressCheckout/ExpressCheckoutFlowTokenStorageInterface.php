<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout;

/**
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout
 */
interface ExpressCheckoutFlowTokenStorageInterface
{
    /**
     * @return array<string, string> Token to cart id of the shopper's session, oldest first, [] when none.
     */
    public function getFlowTokens(): array;

    /**
     * @param array<string, string> $tokens Replaces the map stored in the shopper's session.
     *
     * @return void
     */
    public function saveFlowTokens(array $tokens): void;
}
