<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFoundException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\CredentialsNotFoundException;
use SeQura\Core\BusinessLogic\Domain\OrderReport\Models\SendOrderReportRequest;
use SeQura\Core\BusinessLogic\Domain\OrderReport\ProxyContracts\OrderReportProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Factories\AuthorizedProxyFactory;
use SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport\Requests\SendOrderReportHttpRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OrderReportProxy
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\OrderReport
 */
class OrderReportProxy implements OrderReportProxyInterface
{
    /**
     * @var AuthorizedProxyFactory $authorizedProxyFactory
     */
    private $authorizedProxyFactory;

    /**
     * @param AuthorizedProxyFactory $authorizedProxyFactory
     */
    public function __construct(AuthorizedProxyFactory $authorizedProxyFactory)
    {
        $this->authorizedProxyFactory = $authorizedProxyFactory;
    }

    /**
     * @param SendOrderReportRequest $request
     *
     * @return bool
     *
     * @throws ConnectionDataNotFoundException
     * @throws CredentialsNotFoundException
     * @throws HttpRequestException
     */
    public function sendReport(SendOrderReportRequest $request): bool
    {
        return $this->authorizedProxyFactory->build($request->getMerchant()->getId())
            ->post(new SendOrderReportHttpRequest($request))->isSuccessful();
    }
}
