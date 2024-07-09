<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class Options
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class Options extends OrderRequestDTO
{
    /**
     * @var boolean|null Set to true if $ is jQuery 1.7 or above, false otherwise.
     */
    protected $hasJquery;

    /**
     * @var boolean|null True if the merchant wishes to use the API option that explicitly communicates if some items
     * will be shipped immediately after confirmation.
     */
    protected $usesShippedCart;

    /**
     * @var boolean|null True if the merchant cannot send both addresses during checkout.
     */
    protected $addressesMayBeMissing;

    /**
     * @var boolean|null True if the merchant wishes to lock the provided shopper's personal data.
     */
    protected $immutableCustomerData;

    /**
     * @param bool|null $hasJquery
     * @param bool|null $usesShippedCart
     * @param bool|null $addressesMayBeMissing
     * @param bool|null $immutableCustomerData
     */
    public function __construct(
        bool $hasJquery = null,
        bool $usesShippedCart = null,
        bool $addressesMayBeMissing = null,
        bool $immutableCustomerData = null
    ) {
        $this->hasJquery = $hasJquery;
        $this->usesShippedCart = $usesShippedCart;
        $this->addressesMayBeMissing = $addressesMayBeMissing;
        $this->immutableCustomerData = $immutableCustomerData;
    }

    /**
     * Create a new Options instance from an array of data.
     *
     * @param array $data Array containing the data.
     *
     * @return Options Returns a new Options instance.
     */
    public static function fromArray(array $data): Options
    {
        return new self(
            self::getDataValue($data, 'has_jquery', false),
            self::getDataValue($data, 'uses_shipped_cart', false),
            self::getDataValue($data, 'addresses_may_be_missing', false),
            self::getDataValue($data, 'immutable_customer_data', false)
        );
    }

    /**
     * @return bool|null
     */
    public function getHasJquery(): ?bool
    {
        return $this->hasJquery;
    }

    /**
     * @return bool|null
     */
    public function getUsesShippedCart(): ?bool
    {
        return $this->usesShippedCart;
    }

    /**
     * @return bool|null
     */
    public function getAddressesMayBeMissing(): ?bool
    {
        return $this->addressesMayBeMissing;
    }

    /**
     * @return bool|null
     */
    public function getImmutableCustomerData(): ?bool
    {
        return $this->immutableCustomerData;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
