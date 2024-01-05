<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\ShopPaymentMethod;

/**
 * Class ShopPaymentMethodsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Responses
 */
class ShopPaymentMethodsResponse extends Response
{
    /**
     * @var ShopPaymentMethod[]
     */
    private $paymentMethods;

    /**
     * @param ShopPaymentMethod[]|null $paymentMethods
     */
    public function __construct(?array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $shopPaymentMethods = [];
        foreach ($this->paymentMethods as $paymentMethod) {
            $shopPaymentMethods[] = [
                'code' => $paymentMethod->getCode(),
                'name' => $paymentMethod->getName()
            ];
        }

        return $shopPaymentMethods;
    }
}
