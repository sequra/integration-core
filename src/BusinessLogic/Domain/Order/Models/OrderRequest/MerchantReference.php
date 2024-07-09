<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

/**
 * Class MerchantReference
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
class MerchantReference extends OrderRequestDTO
{
    /**
     * @var string|int Merchant reference 1.
     */
    protected $orderRef1;

    /**
     * @var string|int|null Merchant reference 2.
     */
    protected $orderRef2;

    /**
     * @param int|string $orderRef1
     * @param int|string|null $orderRef2
     */
    public function __construct($orderRef1, $orderRef2 = null)
    {
        $this->orderRef1 = $orderRef1;
        $this->orderRef2 = $orderRef2;
    }

    /**
     * Creates a new MerchantReference instance from an array.
     *
     * @param array $data
     *
     * @return MerchantReference
     */
    public static function fromArray(array $data): MerchantReference
    {
        $orderRef1 = self::getDataValue($data, 'order_ref_1');
        $orderRef2 = self::getDataValue($data, 'order_ref_2');

        return new static($orderRef1, $orderRef2);
    }

    /**
     * @return int|string
     */
    public function getOrderRef1()
    {
        return $this->orderRef1;
    }

    /**
     * @return int|string|null
     */
    public function getOrderRef2()
    {
        return $this->orderRef2;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->transformPropertiesToAnArray(get_object_vars($this));
    }
}
