<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models;

use SeQura\Core\BusinessLogic\Domain\Order\OrderStates;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

/**
 * Class OrderStatusMapping
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models
 */
class OrderStatusMapping
{
    /**
     * @var string
     */
    private $sequraStatus;

    /**
     * @var string
     */
    private $shopStatus;

    /**
     * @param string $seQuraStatus
     * @param string $shopStatus
     *
     * @throws EmptyOrderStatusMappingParameterException
     * @throws InvalidSeQuraOrderStatusException
     */
    public function __construct(string $seQuraStatus, string $shopStatus)
    {
        if(empty($seQuraStatus) || empty($shopStatus)) {
            throw new EmptyOrderStatusMappingParameterException(
                new TranslatableLabel('No parameter can be an empty string.', 400)
            );
        }

        if(!in_array($seQuraStatus, OrderStates::toArray(),true)) {
            throw new InvalidSeQuraOrderStatusException(
                new TranslatableLabel('Invalid SeQura order status.', 400)
            );
        }

        $this->sequraStatus = $seQuraStatus;
        $this->shopStatus = $shopStatus;
    }

    /**
     * @return string
     */
    public function getSequraStatus(): string
    {
        return $this->sequraStatus;
    }

    /**
     * @param string $sequraStatus
     */
    public function setSequraStatus(string $sequraStatus): void
    {
        $this->sequraStatus = $sequraStatus;
    }

    /**
     * @return string
     */
    public function getShopStatus(): string
    {
        return $this->shopStatus;
    }

    /**
     * @param string $shopStatus
     */
    public function setShopStatus(string $shopStatus): void
    {
        $this->shopStatus = $shopStatus;
    }
}
