<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class GetCachedPaymentMethodsRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Requests
 */
class GetCachedPaymentMethodsRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * Optional shipping country code
     *
     * @var string|null
     */
    protected $shippingCountry;

    /**
     * Optional current country code
     *
     * @var string|null
     */
    protected $currentCountry;

    /**
     * @param string $merchantId
     * @param string|null $shippingCountry
     * @param string|null $currentCountry
     */
    public function __construct(string $merchantId, ?string $shippingCountry = null, ?string $currentCountry = null)
    {
        $this->merchantId = $merchantId;
        $this->shippingCountry = $shippingCountry;
        $this->currentCountry = $currentCountry;
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
     * @return string
     */
    public function getShippingCountry(): string
    {
        return (string) $this->shippingCountry;
    }

    /**
     * @param string|null $shippingCountry
     */
    public function setShippingCountry(?string $shippingCountry): void
    {
        $this->shippingCountry = $shippingCountry;
    }

    /**
     * @return string
     */
    public function getCurrentCountry(): string
    {
        return (string) $this->currentCountry;
    }

    /**
     * @param string|null $currentCountry
     */
    public function setCurrentCountry(?string $currentCountry): void
    {
        $this->currentCountry = $currentCountry;
    }

    /**
     * Create a GetAvailablePaymentMethodsRequest instance from an array.
     *
     * @param mixed[] $data
     *
     * @return GetCachedPaymentMethodsRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchant_id'),
            self::getDataValue($data, 'shipping_country', null),
            self::getDataValue($data, 'current_country', null)
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'merchant_id' => $this->merchantId,
            'shipping_country' => $this->shippingCountry,
            'current_country' => $this->currentCountry
        ];
    }
}
