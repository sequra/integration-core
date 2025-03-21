<?php

namespace SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts;

use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\PaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

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
     * @return PaymentMethod[]
     */
    public function getPaymentMethods(): array;

    /**
     * Gets Sequra order by shop order reference
     *
     * @param string $product SeQura product code.
     *
     * @return PaymentMethod|null
     */
    public function getPaymentMethod(string $product): ?PaymentMethod;

    /**
     * Inserts/updates SeQura payment method information.
     *
     * @param PaymentMethod $paymentMethod
     *
     * @return void
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod): void;

    /**
     * Deletes a stored payment method.
     *
     * @param PaymentMethod $paymentMethod
     *
     * @return void
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): void;

    /**
     * Deletes a stored payment method by product code.
     *
     * @param string $product
     *
     * @return void
     *
     * @throws PaymentMethodNotFoundException
     * @throws QueryFilterInvalidParamException
     */
    public function deletePaymentMethodByProductCode(string $product): void;
}
