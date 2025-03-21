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
     * Gets Sequra order by shop order reference
     *
     * @param string $product SeQura product code.
     *
     * @return SeQuraPaymentMethod|null
     */
    public function getPaymentMethodByProduct(string $product): ?SeQuraPaymentMethod;

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
     * Deletes a stored payment method.
     *
     * @param SeQuraPaymentMethod $paymentMethod
     *
     * @return void
     */
    public function deletePaymentMethod(SeQuraPaymentMethod $paymentMethod): void;

    /**
     * Deletes a stored payment method by product code.
     *
     * @param string $product
     *
     * @return void
     *
     * @throws PaymentMethodNotFoundException
     */
    public function deletePaymentMethodByProductCode(string $product): void;
}
