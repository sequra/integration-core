<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class ValidateAssetsKeyRequest
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class ValidateAssetsKeyRequest
{
    /**
     * @var string
     */
    protected $merchantId;
    /**
     * @var array
     */
    protected $paymentMethodIds;
    /**
     * @var string
     */
    protected $assetsKey;
    /**
     * @var string
     */
    protected $mode;

    /**
     * @param string $merchantId
     * @param array $paymentMethodIds
     * @param string $assetsKey
     * @param string $mode
     */
    public function __construct(string $merchantId, array $paymentMethodIds, string $assetsKey, string $mode)
    {
        $this->merchantId = $merchantId;
        $this->paymentMethodIds = $paymentMethodIds;
        $this->assetsKey = $assetsKey;
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    public function getPaymentMethodIds(): array
    {
        return $this->paymentMethodIds;
    }

    public function getAssetsKey(): string
    {
        return $this->assetsKey;
    }
}
