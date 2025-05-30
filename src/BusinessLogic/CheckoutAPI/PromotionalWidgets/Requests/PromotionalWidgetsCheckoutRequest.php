<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class PromotionalWidgetsCheckoutRequest.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\PromotionalWidgets\Requests
 */
class PromotionalWidgetsCheckoutRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $shippingCountry;
    /**
     * @var string
     */
    protected $currentCountry;
    /**
     * @var string
     */
    protected $currentCurrency;
    /**
     * @var string
     */
    protected $currentIpAddress;
    /**
     * @var string
     */
    protected $productId;

    /**
     * @param string $shippingCountry
     * @param string $currentCountry
     * @param string $currentCurrency
     * @param string $currentIpAddress
     * @param string $productId
     */
    public function __construct(
        string $shippingCountry,
        string $currentCountry,
        string $currentCurrency = '',
        string $currentIpAddress = '',
        string $productId = ''
    ) {
        $this->shippingCountry = $shippingCountry;
        $this->currentCountry = $currentCountry;
        $this->currentCurrency = $currentCurrency;
        $this->currentIpAddress = $currentIpAddress;
        $this->productId = $productId;
    }

    /**
     * @return string
     */
    public function getShippingCountry(): string
    {
        return $this->shippingCountry;
    }

    /**
     * @return string
     */
    public function getCurrentCountry(): string
    {
        return $this->currentCountry;
    }

    /**
     * @return string
     */
    public function getCurrentCurrency(): string
    {
        return $this->currentCurrency;
    }

    /**
     * @return string
     */
    public function getCurrentIpAddress(): string
    {
        return $this->currentIpAddress;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @param array<string> $data
     *
     * @return PromotionalWidgetsCheckoutRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'shippingCountry'),
            self::getDataValue($data, 'currentCountry'),
            self::getDataValue($data, 'currentCurrency'),
            self::getDataValue($data, 'currentIpAddress'),
            self::getDataValue($data, 'productId')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['shippingCountry'] = $this->shippingCountry;
        $data['currentCountry'] = $this->currentCountry;
        $data['currentCurrency'] = $this->currentCurrency;
        $data['currentIpAddress'] = $this->currentIpAddress;
        $data['productId'] = $this->productId;

        return $data;
    }
}
