<?php

namespace SeQura\Core\BusinessLogic\Domain\Affiliate\Models;

/**
 * Class AffiliateCancellation.
 *
 * Value object carrying everything the outbound cancellation postback needs. The credentials
 * (offerId / securityToken) are supplied by the caller; the proxy never generates or injects them.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Affiliate\Models
 */
class AffiliateCancellation
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
     * @param string $merchantId Merchant reference, used to resolve the deployment (base URL).
     * @param string $offerId Affiliate offer id.
     * @param string $securityToken Affiliate security token.
     * @param string $transactionId Affiliate transaction (click) id.
     */
    public function __construct(
        string $merchantId,
        string $offerId,
        string $securityToken,
        string $transactionId
    ) {
        $this->merchantId = $merchantId;
        $this->offerId = $offerId;
        $this->securityToken = $securityToken;
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
}
