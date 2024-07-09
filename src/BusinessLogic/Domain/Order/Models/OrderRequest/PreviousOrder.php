<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class PreviousOrder
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class PreviousOrder extends OrderRequestDTO
{
    /**
     * @var string Date (and time, if available) when this order was created or delivered, in ISO-8601 format.
     */
    protected $createdAt;

    /**
     * @var int Total order amount including tax.
     */
    protected $amount;

    /**
     * @var string Currency name for amount.
     */
    protected $currency;

    /**
     * @var string|null The status of the order as reported in the platform.
     */
    protected $rawStatus;

    /**
     * @var string|null The mapped status value.
     */
    protected $status;

    /**
     * @var string|null Payment method as reported by the platform.
     */
    protected $paymentMethodRaw;

    /**
     * @var string|null Mapped payment methods.
     */
    protected $paymentMethod;

    /**
     * @var string|null Previous order's delivery address postal code.
     */
    protected $postalCode;

    /**
     * @var string|null Previous order's country code.
     */
    protected $countryCode;

    /**
     * @param string $createdAt
     * @param int $amount
     * @param string $currency
     * @param string|null $rawStatus
     * @param string|null $status
     * @param string|null $paymentMethodRaw
     * @param string|null $paymentMethod
     * @param string|null $postalCode
     * @param string|null $countryCode
     */
    public function __construct(
        string $createdAt,
        int $amount,
        string $currency,
        string $rawStatus = null,
        string $status = null,
        string $paymentMethodRaw = null,
        string $paymentMethod = null,
        string $postalCode = null,
        string $countryCode = null
    ) {
        $this->createdAt = $createdAt;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->rawStatus = $rawStatus;
        $this->status = $status;
        $this->paymentMethodRaw = $paymentMethodRaw;
        $this->paymentMethod = $paymentMethod;
        $this->postalCode = $postalCode;
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return string|null
     */
    public function getRawStatus(): ?string
    {
        return $this->rawStatus;
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
    public function getPaymentMethodRaw(): ?string
    {
        return $this->paymentMethodRaw;
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
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }

    /**
     * Create a new PreviousOrder instance from an array of data.
     *
     * @param array $data
     *
     * @return PreviousOrder
     */
    public static function fromArray(array $data): PreviousOrder
    {
        return new PreviousOrder(
            self::getDataValue($data, '$created_at'),
            self::getDataValue($data, '$amount', 0),
            self::getDataValue($data, '$currency'),
            self::getDataValue($data, '$raw_status', null),
            self::getDataValue($data, '$status', null),
            self::getDataValue($data, '$payment_method_raw', null),
            self::getDataValue($data, '$payment_method', null),
            self::getDataValue($data, '$postal_code', null),
            self::getDataValue($data, '$country_code', null)
        );
    }
}
