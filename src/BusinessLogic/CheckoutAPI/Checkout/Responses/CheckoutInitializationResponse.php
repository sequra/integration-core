<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\Checkout\Models\CheckoutInitializationData;

/**
 * Class CheckoutInitializationResponse.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Checkout\Responses
 */
class CheckoutInitializationResponse extends Response
{
    /**
     * @var CheckoutInitializationData|null
     */
    protected $initializationData;

    /**
     * @param ?CheckoutInitializationData $initializationData
     */
    public function __construct(?CheckoutInitializationData $initializationData)
    {
        $this->initializationData = $initializationData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->initializationData) {
            return [];
        }

        return [
            'assetKey' => $this->initializationData->getAssetKey(),
            'merchant' => $this->initializationData->getMerchantId(),
            'products' => $this->initializationData->getProducts(),
            'scriptUri' => $this->initializationData->getScriptUri(),
            'locale' => $this->initializationData->getLocale(),
            'currency' => $this->initializationData->getCurrency(),
            'decimalSeparator' => $this->initializationData->getDecimalSeparator(),
            'thousandSeparator' => $this->initializationData->getThousandSeparator(),
        ];
    }
}
