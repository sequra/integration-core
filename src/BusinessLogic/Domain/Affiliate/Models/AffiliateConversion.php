<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\Models;

/**
 * Class AffiliateConversion.
 *
 * Value object carrying everything the outbound conversion postback needs. The credentials
 * (offerId / securityToken) are supplied by the caller (the plugin already holds them from the
 * inbound config); the proxy never generates or injects them.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\Models
 */
class AffiliateConversion
{
    /**
     * @var string $merchantId
     */
    private $merchantId;
    /**
     * @var string $offerId
     */
    private $offerId;
    /**
     * @var string $securityToken
     */
    private $securityToken;
    /**
     * @var string $transactionId
     */
    private $transactionId;
    /**
     * @var float $amount
     */
    private $amount;
    /**
     * @var string $orderReference
     */
    private $orderReference;

    /**
     * @param string $merchantId Merchant reference, used to resolve the deployment (base URL).
     * @param string $offerId Affiliate offer id.
     * @param string $securityToken Affiliate security token.
     * @param string $transactionId Affiliate transaction (click) id.
     * @param float $amount Conversion amount.
     * @param string $orderReference Shop order reference (sent to the affiliate network as adv_sub).
     */
    public function __construct(
        string $merchantId,
        string $offerId,
        string $securityToken,
        string $transactionId,
        float $amount,
        string $orderReference
    ) {
        $this->merchantId = $merchantId;
        $this->offerId = $offerId;
        $this->securityToken = $securityToken;
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
    public function getOfferId(): string
    {
        return $this->offerId;
    }

    /**
     * @return string
     */
    public function getSecurityToken(): string
    {
        return $this->securityToken;
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
}
