<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\Models;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\MerchantReference;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\OrderRequestDTO;

/**
 * Class OrderStatistics
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\Models
 */
class OrderStatistics extends OrderRequestDTO
{
    /**
     * @var string Filed representing the date of order completion.
     */
    protected $completedAt;

    /**
     * @var string Field representing the order currency.
     */
    protected $currency;

    /**
     * @var int|null Field representing the total amount paid or due to pay.
     */
    protected $amount;

    /**
     * @var MerchantReference|null Order id(s) used by the merchant.
     */
    protected $merchantReference;

    /**
     * @var string|null Name or type of payment method.
     */
    protected $paymentMethod;

    /**
     * @var string|null ISO-3166-1 country code of the delivery address.
     */
    protected $country;

    /**
     * @var string|null Device used in purchase.
     */
    protected $device;

    /**
     * @var string|null Status of the order.
     */
    protected $status;

    /**
     * @var string|null Your platform's string representation of the order status.
     */
    protected $rawStatus;

    /**
     * @var bool|null Was the shopper offered to use SeQura in the checkout?
     */
    protected $sequraOffered;

    /**
     * @param string $completedAt
     * @param string $currency
     * @param int|null $amount
     * @param MerchantReference|null $merchantReference
     * @param string|null $paymentMethod
     * @param string|null $country
     * @param string|null $device
     * @param string|null $status
     * @param string|null $rawStatus
     * @param bool|null $sequraOffered
     */
    public function __construct(
        string $completedAt,
        string $currency,
        ?int $amount,
        ?MerchantReference $merchantReference,
        ?string $paymentMethod,
        ?string $country,
        ?string $device,
        ?string $status,
        ?string $rawStatus,
        ?bool $sequraOffered
    ) {
        $this->completedAt = $completedAt;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->merchantReference = $merchantReference;
        $this->paymentMethod = $paymentMethod;
        $this->country = $country;
        $this->device = $device;
        $this->status = $status;
        $this->rawStatus = $rawStatus;
        $this->sequraOffered = $sequraOffered;
    }


    /**
     * Creates a new OrderStatistics instance from an array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $completedAt = self::getDataValue($data, 'completed_at');
        $currency = self::getDataValue($data, 'currency');
        $amount = self::getDataValue($data, 'amount', null);
        $paymentMethod = self::getDataValue($data, 'payment_method', null);
        $country = self::getDataValue($data, 'country', null);
        $device = self::getDataValue($data, 'device', null);
        $status = self::getDataValue($data, 'status', null);
        $rawStatus = self::getDataValue($data, 'raw_status', null);
        $sequraOffered = self::getDataValue($data, 'sequra_offered', null);

        $merchantReference = self::getDataValue($data, 'merchant_reference', null);
        if ($merchantReference !== null) {
            $merchantReference = MerchantReference::fromArray($merchantReference);
        }

        return new self(
            $completedAt,
            $currency,
            $amount,
            $merchantReference,
            $paymentMethod,
            $country,
            $device,
            $status,
            $rawStatus,
            $sequraOffered
        );
    }

    /**
     * @return string
     */
    public function getCompletedAt(): string
    {
        return $this->completedAt;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return int|null
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @return MerchantReference|null
     */
    public function getMerchantReference(): ?MerchantReference
    {
        return $this->merchantReference;
    }

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getDevice(): ?string
    {
        return $this->device;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getRawStatus(): ?string
    {
        return $this->rawStatus;
    }

    /**
     * @return bool|null
     */
    public function getSequraOffered(): ?bool
    {
        return $this->sequraOffered;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
