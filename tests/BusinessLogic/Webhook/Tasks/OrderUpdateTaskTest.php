<?php

namespace SeQura\Core\Tests\BusinessLogic\Webhook\Tasks;

use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockShopOrderStatusesService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class OrderUpdateTaskTest extends BaseSerializationTestCase
{
    /**
     * @return void
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(ShopOrderStatusesServiceInterface::class, static function () {
            return new MockShopOrderStatusesService();
        });

        $webhook = Webhook::fromArray([
            'cart' => '5678',
            'signature' => 'K6hDNSwfcJjF+suAJqXAjA==',
            'order_ref' => 'd168f9bc-de62-4635-be52-0f0c0a5903aa',
            'approved_since' => '3',
            'product_code' => 'i1',
            'sq_state' => 'approved',
            'order_ref_1' => 'ZXCV1234',
        ]);

        $this->serializable = new OrderUpdateTask($webhook);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSuccessfulTaskExecution(): void
    {
        $this->serializable->execute();
    }
}
