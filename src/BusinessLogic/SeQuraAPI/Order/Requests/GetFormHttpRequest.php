<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests;

use SeQura\Core\BusinessLogic\Domain\Order\Models\GetFormRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class GetFormHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Order\Requests
 */
class GetFormHttpRequest extends HttpRequest
{
    /**
     * @param GetFormRequest $request
     */
    public function __construct(GetFormRequest $request)
    {
        parent::__construct(
            '/orders/' . $request->getOrderId() . '/form_v2',
            [],
            $this->transformQueryParameters($request),
            ['Accept' => 'Accept: text/html']
        );
    }

    protected function transformQueryParameters(GetFormRequest $request): array
    {
        $request->getProduct() && $params['product'] = $request->getProduct();
        $request->getCampaign() && $params['campaign'] = $request->getCampaign();
        $request->getAjax() && $params['ajax'] = $request->getAjax();

        return $params ?? [];
    }
}
