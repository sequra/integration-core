<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport;

use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\Requests\SendOrderReportHttpRequest;

/**
 * Class OrderReportProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport
 */
class OrderReportProxy extends AuthorizedProxy implements OrderReportProxyInterface
{
    /**
     * @inheritDoc
     */
    public function sendReport(SendOrderReportRequest $request): bool
    {
        return $this->post(new SendOrderReportHttpRequest($request))->isSuccessful();
    }
}
