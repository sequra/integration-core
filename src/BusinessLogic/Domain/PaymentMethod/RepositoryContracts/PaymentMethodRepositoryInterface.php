<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;

/**
 * Interface PaymentMethodRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts
 */
interface PaymentMethodRepositoryInterface
{
    /**
     * Gets all available payment methods.
     *
     * @param string $merchantId
     *
     * @return SeQuraPaymentMethod[]
     */
    public function getPaymentMethods(string $merchantId): array;

    /**
     * Inserts/updates SeQura payment method information.
     *
     * @param string $merchantId
     * @param SeQuraPaymentMethod $paymentMethod
     *
     * @return void
     */
    public function setPaymentMethod(string $merchantId, SeQuraPaymentMethod $paymentMethod): void;

    /**
     * Deletes a stored payment method by product code.
     *
     * @param string $merchantId
     *
     * @return void
     *
     * @throws PaymentMethodNotFoundException
     */
    public function deletePaymentMethods(string $merchantId): void;

    /**
     * Deletes a stored payment methods.
     *
     * @return void
     */
    public function deleteAllPaymentMethods(): void;
}
