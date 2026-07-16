<?php

namespace SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class SendCancellationRequest.
 *
 * Order-specific data for a cancellation postback. Affiliate credentials are NOT part of this
 * request: the core sources them from the stored AffiliateSettings.
 *
 * @package SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests
 */
class SendCancellationRequest extends DataTransferObject
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
     * @param string $merchantId
     * @param string $transactionId
     */
    public function __construct(string $merchantId, string $transactionId)
    {
        $this->merchantId = $merchantId;
        $this->transactionId = $transactionId;
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
     * @param mixed[] $data
     *
     * @return SendCancellationRequest
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::getDataValue($data, 'merchant_id'),
            self::getDataValue($data, 'transaction_id')
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
        ];
    }
}
