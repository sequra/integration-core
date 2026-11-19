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
    use PaymentMethodResponseTrait;

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
        return array_map([$this, 'paymentMethodToArray'], $this->paymentMethods);
    }
}
