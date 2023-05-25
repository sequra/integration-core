<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Webhook;

use SeQura\Core\BusinessLogic\Domain\Webhook\Models\Webhook;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class WebhookTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $webhook = new Webhook();

        $webhook->setSignature('signature');
        $webhook->setOrderRef('order_ref');
        $webhook->setProductCode('product_code');
        $webhook->setSqState('cancelled');
        $webhook->setOrderRef1('order_ref_1');
        $webhook->setApprovedSince(5);
        $webhook->setNeedsReviewSince(10);

        self::assertEquals('signature', $webhook->getSignature());
        self::assertEquals('order_ref', $webhook->getOrderRef());
        self::assertEquals('product_code', $webhook->getProductCode());
        self::assertEquals('cancelled', $webhook->getSqState());
        self::assertEquals('order_ref_1', $webhook->getOrderRef1());
        self::assertEquals(5, $webhook->getApprovedSince());
        self::assertEquals(10, $webhook->getNeedsReviewSince());
    }
}
