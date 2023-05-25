<?php

namespace SeQura\Core\Tests\BusinessLogic\Webhook\Services;

use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Webhook\Services\StatusMappingService;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class StatusMappingServiceTest extends BaseTestCase
{
    /**
     * @return void
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $mapping = new OrderStatusMapping();
        $mapping->setStoreId('');
        $mapping->setOrderStatusMappingSettings([
            OrderStates::STATE_APPROVED => 'paid',
            OrderStates::STATE_NEEDS_REVIEW => 'in_review',
            OrderStates::STATE_CANCELLED => 'cancelled',
        ]);

        $repository = RepositoryRegistry::getRepository(OrderStatusMapping::getClassName());
        $repository->save($mapping);
    }

    /**
     * @return void
     */
    public function testApprovedStatusMapping()
    {
        /** @var StatusMappingService $service */
        $service = ServiceRegister::getService(StatusMappingService::class);

        $shopStatus = $service->getMapping(OrderStates::STATE_APPROVED);

        self::assertEquals('paid', $shopStatus);
    }

    /**
     * @return void
     */
    public function testReviewStatusMapping()
    {
        /** @var StatusMappingService $service */
        $service = ServiceRegister::getService(StatusMappingService::class);

        $shopStatus = $service->getMapping(OrderStates::STATE_NEEDS_REVIEW);

        self::assertEquals('in_review', $shopStatus);
    }

    /**
     * @return void
     */
    public function testCancelledStatusMapping()
    {
        /** @var StatusMappingService $service */
        $service = ServiceRegister::getService(StatusMappingService::class);

        $shopStatus = $service->getMapping(OrderStates::STATE_CANCELLED);

        self::assertEquals('cancelled', $shopStatus);
    }

    /**
     * @return void
     */
    public function testInvalidStatusMapping()
    {
        /** @var StatusMappingService $service */
        $service = ServiceRegister::getService(StatusMappingService::class);

        $shopStatus = $service->getMapping('test');

        self::assertEquals('', $shopStatus);
    }
}
