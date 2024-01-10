<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\Order;

/**
 * Interface OrderReportServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\Order
 */
interface OrderServiceInterface
{
    /**
     * @param string $merchantReference
     *
     * @return string
     */
    public function getOrderUrl(string $merchantReference): string;
}
