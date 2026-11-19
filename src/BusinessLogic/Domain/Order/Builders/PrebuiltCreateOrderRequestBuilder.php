<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Builders;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;

/**
 * Class PrebuiltCreateOrderRequestBuilder
 *
 * Wraps an already-built CreateOrderRequest so it can be handed to code that expects a
 * CreateOrderRequestBuilder without invoking the (possibly non-idempotent) host builder again.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Builders
 */
class PrebuiltCreateOrderRequestBuilder implements CreateOrderRequestBuilder
{
    /**
     * @var CreateOrderRequest
     */
    private $createOrderRequest;

    /**
     * @param CreateOrderRequest $createOrderRequest
     */
    public function __construct(CreateOrderRequest $createOrderRequest)
    {
        $this->createOrderRequest = $createOrderRequest;
    }

    /**
     * @return CreateOrderRequest
     */
    public function build(): CreateOrderRequest
    {
        return $this->createOrderRequest;
    }
}
