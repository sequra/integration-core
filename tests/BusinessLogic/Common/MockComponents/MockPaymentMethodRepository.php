<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;

/**
 * Class MockPaymentMethodRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockPaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /** @var SeQuraPaymentMethod[] */
    private $paymentMethods = [];

    /**
     * @inheritDoc
     */
    public function getPaymentMethods(string $merchantId): array
    {
        return $this->paymentMethods;
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod(string $merchantId, SeQuraPaymentMethod $paymentMethod): void
    {
        $this->paymentMethods[] = $paymentMethod;
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentMethods(string $merchantId): void
    {
        $this->paymentMethods = [];
    }

    /**
     * @param SeQuraPaymentMethod[] $paymentMethods
     *
     * @return void
     */
    public function setMockPaymentMethods(array $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }

    /**
     * @return void
     */
    public function deleteAllPaymentMethods(): void
    {
        $this->paymentMethods = [];
    }
}
