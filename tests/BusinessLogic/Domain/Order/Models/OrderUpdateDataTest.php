<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order\Models;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTrackingException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupStore;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPostal;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderUpdateData;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class OrderUpdateDataTest extends BaseTestCase
{
    /**
     * @throws Exception
     */
    public function testTrackingsSurviveTheArrayRoundTrip(): void
    {
        $data = new OrderUpdateData('ZXCV1234', null, null, null, null, [
            new TrackingPostal('shipment-1', 'DHL', '00340434161094042557'),
            new TrackingPickupStore('shipment-2', null, null, 'operator-1', 'store-9'),
        ]);

        $restored = OrderUpdateData::fromArray($data->toArray());

        self::assertInstanceOf(TrackingPostal::class, $restored->getTrackings()[0]);
        self::assertInstanceOf(TrackingPickupStore::class, $restored->getTrackings()[1]);
        self::assertEquals($data->toArray(), $restored->toArray());
    }

    /**
     * @throws Exception
     */
    public function testMissingTrackingsStayNullAfterTheArrayRoundTrip(): void
    {
        $data = new OrderUpdateData('ZXCV1234', null, null, null, null);

        $restored = OrderUpdateData::fromArray($data->toArray());

        self::assertNull($restored->getTrackings());
    }

    /**
     * @throws Exception
     */
    public function testRejectsATrackingThatIsNotATrackingInstance(): void
    {
        $this->expectException(InvalidTrackingException::class);

        new OrderUpdateData('ZXCV1234', null, null, null, null, [
            ['type' => 'postal', 'reference' => 'shipment-1', 'carrier' => 'DHL'],
        ]);
    }
}
