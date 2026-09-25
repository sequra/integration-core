<?php

namespace SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\OrderIdentifiersProviderInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\StoreInfo\StoreInfoServiceInterface;

/**
 * Class OrderIdentifiersService
 *
 * Publishes the order identifiers the integration can send to SeQura as the primary
 * order reference. Which values exist is a property of the shop platform, so they
 * come from the integration; a platform that identifies an order by a single value
 * publishes none and the choice is not offered for it.
 *
 * @package SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services
 */
class OrderIdentifiersService
{
    /**
     * @var StoreInfoServiceInterface
     */
    protected $storeInfoService;

    /**
     * @param StoreInfoServiceInterface $storeInfoService
     */
    public function __construct(StoreInfoServiceInterface $storeInfoService)
    {
        $this->storeInfoService = $storeInfoService;
    }

    /**
     * Returns the order identifiers of the integration, as a map of the value stored
     * in the general settings to the label describing it to the merchant. Empty for an
     * integration that does not publish any.
     *
     * @return array<string, string>
     */
    public function getAvailableOrderIdentifiers(): array
    {
        if (!$this->storeInfoService instanceof OrderIdentifiersProviderInterface) {
            return [];
        }

        return $this->storeInfoService->getAvailableOrderIdentifiers();
    }
}
