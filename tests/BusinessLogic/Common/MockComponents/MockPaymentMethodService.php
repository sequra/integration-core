<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;

/**
 * Class MockPaymentMethodService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockPaymentMethodService extends PaymentMethodsService
{
    /** @var SeQuraPaymentMethod[] */
    private $paymentMethods = [];

    /**
     * Gets available payment methods for merchant.
     *
     * @param string $merchantId
     * @param bool $cache
     *
     * @return SeQuraPaymentMethod[]
     */
    public function getMerchantsPaymentMethods(string $merchantId, bool $cache = false): array
    {
        return [];
    }

    /**
     * Gets available products for the merchant.
     *
     * @param string $merchantId
     *
     * @return string[]
     */
    public function getMerchantProducts(string $merchantId): array
    {
        return [];
    }

    /**
     * Returns cached SeQura payment methods.
     *
     * @return SeQuraPaymentMethod[]
     */
    public function getCachedPaymentMethods(string $merchantId): array
    {
        return $this->paymentMethods;
    }

    /**
     * @param array $paymentMethods
     *
     * @return void
     */
    public function setMockPaymentMethods(array $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }
}
