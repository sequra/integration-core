<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ShopPaymentMethods;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\ShopPaymentMethod;

/**
 * Interface ShopOrderStatusesServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ShopPaymentMethods
 */
interface ShopPaymentMethodsServiceInterface
{
    /**
     * Returns offline payment methods from the shop system.
     *
     * @return ShopPaymentMethod[]
     */
    public function getShopPaymentMethods(): array;
}
