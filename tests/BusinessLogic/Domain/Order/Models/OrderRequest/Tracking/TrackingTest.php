<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Order\Exceptions\InvalidTrackingTypeException;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\Tracking;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupPoint;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPickupStore;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\Tracking\TrackingPostal;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

class TrackingTest extends BaseTestCase
{
    /**
     * @throws Exception
     */
    public function testFromArrayBuildsTheSubtypeTheTypeNames(): void
    {
        $postal = new TrackingPostal('shipment-1', 'DHL', '00340434161094042557');
        $pickupPoint = new TrackingPickupPoint('shipment-2', 'PP-778');
        $pickupStore = new TrackingPickupStore('shipment-3', null, null, 'operator-1', 'store-9');

        self::assertEquals($postal, Tracking::fromArray($postal->toArray()));
        self::assertEquals($pickupPoint, Tracking::fromArray($pickupPoint->toArray()));
        self::assertEquals($pickupStore, Tracking::fromArray($pickupStore->toArray()));
    }

    /**
     * @throws Exception
     */
    public function testFromArrayRejectsAnUnknownType(): void
    {
        $this->expectException(InvalidTrackingTypeException::class);

        Tracking::fromArray(['type' => 'drone', 'reference' => 'shipment-1']);
    }

    /**
     * @throws Exception
     */
    public function testFromArrayRejectsAMissingType(): void
    {
        $this->expectException(InvalidTrackingTypeException::class);

        Tracking::fromArray(['reference' => 'shipment-1', 'carrier' => 'DHL']);
    }

    /**
     * @throws Exception
     */
    public function testFromArrayDoesNotTakeAZeroTypeForPostal(): void
    {
        $this->expectException(InvalidTrackingTypeException::class);

        Tracking::fromArray(['type' => 0, 'reference' => 'shipment-1', 'carrier' => 'DHL']);
    }

    /**
     * @throws Exception
     */
    public function testFromArrayRejectsATypeThatIsNotText(): void
    {
        $this->expectException(InvalidTrackingTypeException::class);

        Tracking::fromArray(['type' => ['postal'], 'reference' => 'shipment-1', 'carrier' => 'DHL']);
    }
}
