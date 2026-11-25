<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses;

use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;

/**
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Responses
 */
class ExpressCheckoutCartSummaryResponse extends ExpressCheckoutSolicitResponse
{
    /**
     * @var string
     */
    protected $flowToken;

    /**
     * @var array<string, mixed>
     */
    protected $cartData;

    /**
     * @param SeQuraForm $identificationForm
     * @param string|null $orderUuid
     * @param string|null $metadataEndpoint
     * @param string|null $formUrl
     * @param string $flowToken
     * @param array<string, mixed> $cartData
     */
    public function __construct(
        SeQuraForm $identificationForm,
        ?string $orderUuid,
        ?string $metadataEndpoint,
        ?string $formUrl,
        string $flowToken,
        array $cartData
    ) {
        parent::__construct($identificationForm, $orderUuid, $metadataEndpoint, $formUrl);
        $this->flowToken = $flowToken;
        $this->cartData = $cartData;
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'flowToken' => $this->flowToken,
            'cartData' => $this->cartData,
        ]);
    }
}
