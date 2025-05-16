<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetWidgetsCheckoutRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests
 */
class GetWidgetsCheckoutRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @param string $merchantId
     */
    public function __construct(string $merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): void
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param array<string> $data
     *
     * @return GetWidgetsCheckoutRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchantId')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['merchantId'] = $this->merchantId;

        return $data;
    }
}
