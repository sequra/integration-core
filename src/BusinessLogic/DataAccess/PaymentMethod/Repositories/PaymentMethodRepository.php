<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\PaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\PaymentMethodNotFoundException;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class PaymentMethodRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Repositories
 */
class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @var RepositoryInterface Cached payment methods repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * PaymentMethodRepository constructor.
     *
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethods(): array
    {
        return array_filter(
            $this->repository->select(),
            function ($paymentMethod) {
                return $paymentMethod instanceof PaymentMethod;
            }
        );
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentMethod(string $product): ?PaymentMethod
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('product', Operators::EQUALS, $product);
        $paymentMethod = $this->repository->selectOne($queryFilter);

        if ($paymentMethod instanceof PaymentMethod) {
            return $paymentMethod;
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod): void
    {
        $savedPaymentMethod = $this->getPaymentMethod($paymentMethod->getProduct());
        if (!$savedPaymentMethod) {
            $paymentMethod->setStoreId($this->storeContext->getStoreId());
            $this->repository->save($paymentMethod);

            return;
        }

        $paymentMethod->setId($savedPaymentMethod->getId());
        $this->repository->update($paymentMethod);
    }

    /**
     * @inheritDoc
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod): void
    {
        $this->repository->delete($paymentMethod);
    }


    /**
     * @inheritDoc
     */
    public function deletePaymentMethodByProductCode(string $product): void
    {
        $paymentMethod = $this->getPaymentMethod($product);

        if ($paymentMethod === null) {
            throw new PaymentMethodNotFoundException("Payment method with product code $product not found");
        }

        $this->repository->delete($paymentMethod);
    }
}
