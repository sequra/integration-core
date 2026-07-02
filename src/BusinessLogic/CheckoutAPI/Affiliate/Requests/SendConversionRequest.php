<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class SendConversionRequest.
 *
 * Order-specific data for a conversion postback. Affiliate credentials are NOT part of this
 * request: the core sources them from the stored AffiliateSettings.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests
 */
class SendConversionRequest extends DataTransferObject
{
    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $orderReference;

    /**
     * @param string $merchantId
     * @param string $transactionId
     * @param float $amount
     * @param string $orderReference
     */
    public function __construct(string $merchantId, string $transactionId, float $amount, string $orderReference)
    {
        $this->merchantId = $merchantId;
        $this->transactionId = $transactionId;
        $this->amount = $amount;
        $this->orderReference = $orderReference;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getOrderReference(): string
    {
        return $this->orderReference;
    }

    /**
     * @param mixed[] $data
     *
     * @return SendConversionRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchant_id'),
            self::getDataValue($data, 'transaction_id'),
            (float) self::getDataValue($data, 'amount', 0),
            self::getDataValue($data, 'order_reference')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'merchant_id' => $this->merchantId,
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'order_reference' => $this->orderReference,
        ];
    }
}
