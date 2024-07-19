<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\BaseOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\UpdateOrderRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class UpdateOrderHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class UpdateOrderHttpRequest extends HttpRequest
{
    /**
     * @param string $merchantId
     * @param string $shopOrderReference
     * @param UpdateOrderRequest $request
     */
    public function __construct(string $merchantId, string $shopOrderReference, UpdateOrderRequest $request)
    {
        parent::__construct(
            '/merchants/' . $merchantId . '/orders/' . $shopOrderReference,
            $this->transformBody($request)
        );
    }

    /**
     * Transforms the request body to be encapsulated in order property.
     *
     * @param BaseOrderRequest $request
     *
     * @return array
     */
    protected function transformBody(BaseOrderRequest $request): array
    {
        $data['order'] = $request->toArray();

        return $data;
    }
}
