<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\Requests;

use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;

/**
 * Class SendOrderReportHttpRequest
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\Requests
 */
class SendOrderReportHttpRequest extends HttpRequest
{
    /**
     * @param SendOrderReportRequest $request
     */
    public function __construct(SendOrderReportRequest $request)
    {
        parent::__construct('/orders/delivery_reports', $this->transformBody($request));
    }

    /**
     * Transforms the request body to be encapsulated in "delivery_report" property.
     *
     * @param SendOrderReportRequest $request
     *
     * @return array
     */
    protected function transformBody(SendOrderReportRequest $request): array
    {
        $data['delivery_report'] = $request->toArray();

        return $data;
    }
}
