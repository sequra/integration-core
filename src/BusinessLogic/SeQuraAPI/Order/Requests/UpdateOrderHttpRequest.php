<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\BaseOrderRequest;
use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\CreateOrderRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class UpdateOrderHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class UpdateOrderHttpRequest extends HttpRequest
{
    /**
     * @param string $id
     * @param CreateOrderRequest $request
     */
    public function __construct(string $id, CreateOrderRequest $request)
    {
        parent::__construct('/orders/' . $id, $this->transformBody($request));
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
