<?php

namespace SeQura\Core\BusinessLogic\Domain\Webhook\Services;

/**
 * Class OrderStatusProvider
 *
 * @package SeQura\Core\BusinessLogic\Domain\Webhook\Services
 */
interface OrderStatusProvider
{
    /**
     * Returns the order state mapping for the provided state.
     *
     * @param string $state
     *
     * @return string
     */
    public function getMapping(string $state): string;
}
