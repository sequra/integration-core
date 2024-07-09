<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest\BaseOrderRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class AcknowledgeOrderHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class AcknowledgeOrderHttpRequest extends HttpRequest
{
    /**
     * @param string $id
     * @param BaseOrderRequest $request
     */
    public function __construct(string $id, BaseOrderRequest $request)
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
    protected function transformBody(BaseOrderRequest $request): array
    {
        $data['order'] = $request->toArray();

        return $data;
    }
}
