<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Class SolicitationResponse
 *
 * A successful response with a null order and no payment methods means the cart is not eligible
 * for SeQura (unsupported shipping country, or a GeneralSettings exclusion) — an expected shopper
 * state rather than a failure.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response
 */
class SolicitationResponse extends Response
{
    /**
     * @var SeQuraOrder|null
     */
    protected $order;
    /**
     * @var SeQuraPaymentMethod[]
     */
    protected $availablePaymentMethods;

    /**
     * SolicitationResponse constructor.
     *
     * @param SeQuraOrder|null $order Null when the cart is not eligible for SeQura.
     * @param SeQuraPaymentMethod[] $availablePaymentMethods
     */
    public function __construct(?SeQuraOrder $order, array $availablePaymentMethods)
    {
        $this->order = $order;
        $this->availablePaymentMethods = $availablePaymentMethods;
    }

    /**
     * @return SeQuraOrder|null
     */
    public function getSolicitedOrder(): ?SeQuraOrder
    {
        return $this->order;
    }

    /**
     * @return SeQuraPaymentMethod[]
     */
    public function getAvailablePaymentMethods(): array
    {
        return $this->availablePaymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'order' => $this->order ? $this->order->toArray() : null,
            'availablePaymentMethods' => array_map(static function (SeQuraPaymentMethod $paymentMethod) {
                return [
                    'product' => $paymentMethod->getProduct(),
                    'title' => $paymentMethod->getTitle(),
                    'long_title' => $paymentMethod->getLongTitle(),
                    'starts_at' => $paymentMethod->getStartsAt()->getTimestamp(),
                    'ends_at' => $paymentMethod->getEndsAt()->getTimestamp(),
                    'campaign' => $paymentMethod->getCampaign(),
                    'claim' => $paymentMethod->getClaim(),
                    'description' => $paymentMethod->getDescription(),
                    'icon' => $paymentMethod->getIcon(),
                    'cost_description' => $paymentMethod->getCostDescription(),
                    'min_amount' => $paymentMethod->getMinAmount(),
                    'max_amount' => $paymentMethod->getMaxAmount(),
                    'cost' => [
                        'setupFee' => $paymentMethod->getCost()->getSetupFee(),
                        'instalmentFee' => $paymentMethod->getCost()->getInstalmentFee(),
                        'downPaymentFees' => $paymentMethod->getCost()->getDownPaymentFees(),
                        'instalmentTotal' => $paymentMethod->getCost()->getInstalmentTotal(),
                    ],
                ];
            }, $this->availablePaymentMethods)
        ];
    }
}
