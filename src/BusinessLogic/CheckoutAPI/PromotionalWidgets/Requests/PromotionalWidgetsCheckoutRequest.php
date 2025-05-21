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
     * @param string $shippingCountry
     * @param string $currentCountry
     */
    public function __construct(string $shippingCountry, string $currentCountry)
    {
        $this->shippingCountry = $shippingCountry;
        $this->currentCountry = $currentCountry;
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
     * @param array<string> $data
     *
     * @return PromotionalWidgetsCheckoutRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'shippingCountry'),
            self::getDataValue($data, 'currentCountry')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data['shippingCountry'] = $this->shippingCountry;
        $data['currentCountry'] = $this->currentCountry;

        return $data;
    }
}
