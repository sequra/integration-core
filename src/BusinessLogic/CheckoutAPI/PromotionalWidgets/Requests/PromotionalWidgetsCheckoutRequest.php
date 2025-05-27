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
     * @param string $shippingCountry
     * @param string $currentCountry
     * @param string $currentCurrency
     * @param string $currentIpAddress
     */
    public function __construct(
        string $shippingCountry,
        string $currentCountry,
        string $currentCurrency = '',
        string $currentIpAddress = ''
    ) {
        $this->shippingCountry = $shippingCountry;
        $this->currentCountry = $currentCountry;
        $this->currentCurrency = $currentCurrency;
        $this->currentIpAddress = $currentIpAddress;
    }

    public function getShippingCountry(): string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(string $shippingCountry): void
    {
        $this->shippingCountry = $shippingCountry;
    }

    public function getCurrentCountry(): string
    {
        return $this->currentCountry;
    }

    public function setCurrentCountry(string $currentCountry): void
    {
        $this->currentCountry = $currentCountry;
    }

    /**
     * @return string
     */
    public function getCurrentCurrency(): string
    {
        return $this->currentCurrency;
    }

    /**
     * @param string $currentCurrency
     */
    public function setCurrentCurrency(string $currentCurrency): void
    {
        $this->currentCurrency = $currentCurrency;
    }

    /**
     * @return string
     */
    public function getCurrentIpAddress(): string
    {
        return $this->currentIpAddress;
    }

    /**
     * @param string $currentIpAddress
     */
    public function setCurrentIpAddress(string $currentIpAddress): void
    {
        $this->currentIpAddress = $currentIpAddress;
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
            self::getDataValue($data, 'currentIpAddress')
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

        return $data;
    }
}
