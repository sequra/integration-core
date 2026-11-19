<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts;

use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Interface OrderReportProxyInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts
 */
interface OrderReportProxyInterface
{
    /**
     * Sends a new order report to the SeQura API.
     *
     * @param SendOrderReportRequest $request
     *
     * @return bool
     *
     * @throws HttpRequestException
     */
    public function sendReport(SendOrderReportRequest $request): bool;
}
