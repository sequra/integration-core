<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement\Responses;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\OrderManagement\Responses\OrderUpdateResponse;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class OrderUpdateResponseTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\OrderManagement\Responses
 */
class OrderUpdateResponseTest extends BaseTestCase
{
    /**
     * @return void
     *
     * @throws Exception
     */
    public function testItCarriesTheUpdatedOrder(): void
    {
        // Arrange
        $order = $this->seQuraOrder();

        // Act
        $response = new OrderUpdateResponse($order);

        // Assert
        self::assertSame($order, $response->getOrder());
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testItIsTransformedToTheOrderItself(): void
    {
        // Arrange
        $order = $this->seQuraOrder();

        // Act
        $response = new OrderUpdateResponse($order);

        // Assert
        self::assertEquals($order->toArray(), $response->toArray());
    }

    /**
     * @return SeQuraOrder
     *
     * @throws Exception
     */
    private function seQuraOrder(): SeQuraOrder
    {
        $order = file_get_contents(__DIR__ . '/../../../Common/MockObjects/SeQuraOrder.json');

        return SeQuraOrder::fromArray(json_decode($order, true)['order']);
    }
}
