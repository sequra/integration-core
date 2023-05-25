<?php

namespace SeQura\Core\BusinessLogic\Webhook\Services;

use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;
use SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository;

/**
 * Class StatusMappingService
 *
 * @package SeQura\Core\BusinessLogic\Webhook\Services
 */
class StatusMappingService implements OrderStatusProvider
{
    /**
     * @var OrderStatusMappingRepository
     */
    private $repository;

    /**
     * StatusMappingService constructor.
     *
     * @param OrderStatusMappingRepository $repository
     */
    public function __construct(OrderStatusMappingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the order state mapping for the provided state.
     *
     * @param string $state
     *
     * @return string
     */
    public function getMapping(string $state): string
    {
        $mapping = $this->getOrderStatusMappingSettings();

        return array_key_exists($state, $mapping) ? $mapping[$state] : '';
    }


    /**
     * Retrieves either the stored order status mappings or the default ones.
     *
     * @return array
     */
    public function getOrderStatusMappingSettings(): array
    {
        $orderStatusMapping = $this->repository->getOrderStatusMapping();

        return !empty($orderStatusMapping) ? $orderStatusMapping : $this->getDefaultStatusMapping();
    }

    /**
     * Returns default status mappings.
     *
     * @return array
     */
    protected function getDefaultStatusMapping(): array
    {
        return [
            OrderStates::STATE_APPROVED => '',
            OrderStates::STATE_NEEDS_REVIEW => '',
            OrderStates::STATE_CANCELLED => '',
        ];
    }
}
