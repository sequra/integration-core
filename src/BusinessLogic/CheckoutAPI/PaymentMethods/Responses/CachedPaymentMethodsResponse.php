<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Class CachedPaymentMethodsResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Responses
 */
class CachedPaymentMethodsResponse extends Response
{
    /**
     * @var SeQuraPaymentMethod[]
     */
    protected $paymentMethods;

    /**
     * @param SeQuraPaymentMethod[] $paymentMethods
     */
    public function __construct(array $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $methods = [];
        foreach ($this->paymentMethods as $paymentMethod) {
            $methods[] = [
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
                'maxAmount' => $paymentMethod->getMaxAmount()
            ];
        }

        return $methods;
    }
}
