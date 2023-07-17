<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Builders;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;

/**
 * Interface CreateOrderRequestBuilder
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Builders
 */
interface CreateOrderRequestBuilder
{
    public function build(): CreateOrderRequest;
}
