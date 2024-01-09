<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\BaseOrderRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class UpdateOrderCartsHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class UpdateOrderCartsHttpRequest extends HttpRequest
{
    /**
     * @param string $id
     * @param BaseOrderRequest $request
     */
    public function __construct(string $id, BaseOrderRequest $request)
    {
        parent::__construct('/merchants/' . $request->getMerchant()->getId() . '/orders/' . $id, $this->transformBody($request));
    }

    /**
     * Transforms the request body to be encapsulated in order property.
     *
     * @param BaseOrderRequest $request
     *
     * @return array
     */
    private function transformBody(BaseOrderRequest $request): array
    {
        $data['order'] = $request->toArray();

        return $data;
    }
}
