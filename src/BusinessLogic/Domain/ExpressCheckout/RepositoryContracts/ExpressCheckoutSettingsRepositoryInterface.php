<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;

/**
 * Interface ExpressCheckoutSettingsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts
 */
interface ExpressCheckoutSettingsRepositoryInterface
{
    /**
     * Returns Express Checkout settings for the current store context.
     *
     * @return ExpressCheckoutSettings|null
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings;

    /**
     * Insert/update Express Checkout settings for the current store context.
     *
     * @param ExpressCheckoutSettings $settings
     *
     * @return void
     */
    public function setExpressCheckoutSettings(ExpressCheckoutSettings $settings): void;

    /**
     * Deletes Express Checkout settings for the current store context.
     *
     * @return void
     */
    public function deleteExpressCheckoutSettings(): void;
}
