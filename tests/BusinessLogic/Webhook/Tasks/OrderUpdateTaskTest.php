<?php

namespace SeQura\Core\Tests\BusinessLogic\Webhook\Tasks;

use SeQura\Core\Tests\BusinessLogic\Common\BaseSerializationTestCase;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\BusinessLogic\Webhook\Tasks\OrderUpdateTask;

class OrderUpdateTaskTest extends BaseSerializationTestCase
{
    /**
     * @return void
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

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
