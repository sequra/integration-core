<?php

namespace SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models;

/**
 * @package SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models
 */
class SolicitedCartSummary
{
    /**
     * @var ExpressCheckoutForm
     */
    protected $form;

    /**
     * @var string
     */
    protected $flowToken;

    /**
     * @var array<string, mixed>
     */
    protected $cartData;

    /**
     * @param ExpressCheckoutForm $form
     * @param string $flowToken
     * @param array<string, mixed> $cartData
     */
    public function __construct(ExpressCheckoutForm $form, string $flowToken, array $cartData)
    {
        $this->form = $form;
        $this->flowToken = $flowToken;
        $this->cartData = $cartData;
    }

    /**
     * @return ExpressCheckoutForm
     */
    public function getForm(): ExpressCheckoutForm
    {
        return $this->form;
    }

    /**
     * @return string
     */
    public function getFlowToken(): string
    {
        return $this->flowToken;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCartData(): array
    {
        return $this->cartData;
    }
}
