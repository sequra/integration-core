<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services;

use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\OrderStatusProvider;

/**
 * Class OrderStatusSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services
 */
class OrderStatusSettingsService implements OrderStatusProvider
{
    /**
     * @var OrderStatusSettingsRepositoryInterface
     */
    private $orderStatusSettingsRepository;

    /**
     * @param OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository
     */
    public function __construct(OrderStatusSettingsRepositoryInterface $orderStatusSettingsRepository)
    {
        $this->orderStatusSettingsRepository = $orderStatusSettingsRepository;
    }

    /**
     * Retrieves order status settings from the database via order status settings repository.
     *
     * @return OrderStatusMapping[]|null
     */
    public function getOrderStatusSettings(): ?array
    {
        return $this->orderStatusSettingsRepository->getOrderStatusMapping() ?? $this->getDefaultStatusMappings();
    }

    /**
     * Calls the repository to save the order status settings to the database.
     *
     * @param OrderStatusMapping[] $orderStatusMappings
     *
     * @return void
     */
    public function saveOrderStatusSettings(array $orderStatusMappings): void
    {
        $this->orderStatusSettingsRepository->setOrderStatusMapping($orderStatusMappings);
    }

    /**
     * @inheritDoc
     */
    public function getMapping(string $state): string
    {
        $mappings = $this->getOrderStatusSettings();
        foreach ($mappings as $mapping) {
            if ($mapping->getSequraStatus() === $state) {
                return $mapping->getShopStatus();
            }
        }

        return '';
    }

    /**
     * Returns default status mappings.
     *
     * @return OrderStatusMapping[]
     */
    protected function getDefaultStatusMappings(): array
    {
        return [
            new OrderStatusMapping(OrderStates::STATE_APPROVED, ''),
            new OrderStatusMapping(OrderStates::STATE_NEEDS_REVIEW, ''),
            new OrderStatusMapping(OrderStates::STATE_CANCELLED, '')
        ];
    }
}
