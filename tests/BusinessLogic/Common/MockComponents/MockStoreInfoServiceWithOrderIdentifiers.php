<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\OrderIdentifiersProviderInterface;

/**
 * Class MockStoreInfoServiceWithOrderIdentifiers.
 *
 * Stands in for an integration whose platform identifies an order by more than one
 * value and therefore publishes the order identifiers it can send.
 *
 * @package Common\MockComponents
 */
class MockStoreInfoServiceWithOrderIdentifiers extends MockStoreInfoService implements OrderIdentifiersProviderInterface
{
    /**
     * @var array<string, string>
     */
    private $orderIdentifiers = [];

    /**
     * @inheritDoc
     */
    public function getAvailableOrderIdentifiers(): array
    {
        return $this->orderIdentifiers;
    }

    /**
     * @param array<string, string> $orderIdentifiers
     *
     * @return void
     */
    public function setMockOrderIdentifiers(array $orderIdentifiers): void
    {
        $this->orderIdentifiers = $orderIdentifiers;
    }
}
