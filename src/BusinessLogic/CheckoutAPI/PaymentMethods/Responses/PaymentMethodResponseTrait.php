<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Trait PaymentMethodResponseTrait
 *
 * Shared serialization of a payment method for the storefront, so it reads one shape whichever checkout
 * endpoint it calls. Written out here rather than delegated to the model, whose own toArray() is the format
 * the payment method is stored in.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses
 */
trait PaymentMethodResponseTrait
{
    /**
     * @param SeQuraPaymentMethod $paymentMethod
     *
     * @return mixed[]
     */
    protected function paymentMethodToArray(SeQuraPaymentMethod $paymentMethod): array
    {
        return [
            'product' => $paymentMethod->getProduct(),
            'title' => $paymentMethod->getTitle(),
            'longTitle' => $paymentMethod->getLongTitle(),
            'cost' => [
                'setupFee' => $paymentMethod->getCost()->getSetupFee(),
                'instalmentFee' => $paymentMethod->getCost()->getInstalmentFee(),
                'downPaymentFees' => $paymentMethod->getCost()->getDownPaymentFees(),
                'instalmentTotal' => $paymentMethod->getCost()->getInstalmentTotal(),
            ],
            'startsAt' => $paymentMethod->getStartsAt()->format('Y-m-d H:i:s'),
            'endsAt' => $paymentMethod->getEndsAt()->format('Y-m-d H:i:s'),
            'campaign' => $paymentMethod->getCampaign(),
            'claim' => $paymentMethod->getClaim(),
            'description' => $paymentMethod->getDescription(),
            'icon' => $paymentMethod->getIcon(),
            'costDescription' => $paymentMethod->getCostDescription(),
            'minAmount' => $paymentMethod->getMinAmount(),
            'maxAmount' => $paymentMethod->getMaxAmount(),
        ];
    }
}
