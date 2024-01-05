<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\ShopPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopPaymentMethods\ShopPaymentMethodsServiceInterface;

/**
 * Class MockShopPaymentMethodsService
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockShopPaymentMethodsService implements ShopPaymentMethodsServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getShopPaymentMethods(): array
    {
        return [
            new ShopPaymentMethod('card', 'Credit Card'),
            new ShopPaymentMethod('cash', 'Cash')
        ];
    }
}
