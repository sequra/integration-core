<?php

namespace SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\PaymentMethod\Entities\PaymentMethod;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Models\SeQuraPaymentMethod;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\RepositoryContracts\PaymentMethodRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
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
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getPaymentMethods(string $merchantId): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());
        $queryFilter->where('merchantId', Operators::EQUALS, $merchantId);

        /**
         * @var PaymentMethod[] $paymentMethods
         */
        $paymentMethods = $this->repository->select($queryFilter);
        return array_map(
            function (PaymentMethod $paymentMethod) {
                return $paymentMethod->getSequraPaymentMethod();
            },
            $paymentMethods
        );
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setPaymentMethod(string $merchantId, SeQuraPaymentMethod $paymentMethod): void
    {
        /**
         * @var ?PaymentMethod $paymentMethodEntity
         */
        $paymentMethodEntity = $this->getPaymentMethodEntity($paymentMethod, $merchantId);

        if ($paymentMethodEntity === null) {
            $paymentMethodEntity = new PaymentMethod();

            $paymentMethodEntity->setStoreId($this->storeContext->getStoreId());
            $paymentMethodEntity->setMerchantId($merchantId);
            $paymentMethodEntity->setProduct($paymentMethod->getProduct());
            $paymentMethodEntity->setSeQuraPaymentMethod($paymentMethod);
            $this->repository->save($paymentMethodEntity);

            return;
        }

        $paymentMethodEntity->setSeQuraPaymentMethod($paymentMethod);
        $this->repository->update($paymentMethodEntity);
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deletePaymentMethods(string $merchantId): void
    {
        $paymentMethods = $this->getPaymentMethods($merchantId);

        foreach ($paymentMethods as $paymentMethod) {
            $this->deletePaymentMethod($paymentMethod, $merchantId);
        }
    }

    /**
     * Returns the payment method entity.
     *
     * @param SeQuraPaymentMethod $paymentMethod
     * @param string $merchantId
     *
     * @return Entity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    private function getPaymentMethodEntity(SeQuraPaymentMethod $paymentMethod, string $merchantId): ?Entity
    {
        $filter = new QueryFilter();
        $filter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('product', Operators::EQUALS, $paymentMethod->getProduct())
            ->where('merchantId', Operators::EQUALS, $merchantId);

        return $this->repository->selectOne($filter);
    }

    /**
     * @param SeQuraPaymentMethod $paymentMethod
     * @param string $merchantId
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    private function deletePaymentMethod(SeQuraPaymentMethod $paymentMethod, string $merchantId): void
    {
        /**
         * @var PaymentMethod $paymentMethodEntity
         */
        $paymentMethodEntity = $this->getPaymentMethodEntity($paymentMethod, $merchantId);
        $this->repository->delete($paymentMethodEntity);
    }
}
