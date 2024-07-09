<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\BaseOrderRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class CreateOrderHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class CreateOrderHttpRequest extends HttpRequest
{
    /**
     * @param BaseOrderRequest $request
     */
    public function __construct(BaseOrderRequest $request)
    {
        parent::__construct('/orders', $this->transformBody($request));
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
