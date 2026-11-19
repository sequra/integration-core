<?php

namespace SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate;

use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;

/**
 * Class AffiliateHttpProxy.
 *
 * Base (unauthenticated) proxy for affiliate postbacks, which the runtime router forwards verbatim
 * to its destination -- the affiliate network (an external third party) for conversions, or
 * seQura's own backend for cancellations. The GET
 * conversion goes out as a clean GET -- no request body and no Content-Type header -- because a
 * GET carrying a body (the framework's default `"[]"` + `application/json`) can be rejected by the
 * destination or an intermediary such as a WAF/CDN, which would silently drop the conversion. The
 * whole payload already travels in the query string. POST cancellations keep the normal JSON body
 * and Content-Type. This is scoped here on purpose so BaseProxy behaviour is unchanged elsewhere.
 *
 * @package SeQura\Core\BusinessLogic\SeQuraAPI\Affiliate
 */
class AffiliateHttpProxy extends BaseProxy
{
    /**
     * @inheritDoc
     */
    protected function call(string $method, HttpRequest $request): HttpResponse
    {
        if ($method !== HttpClient::HTTP_METHOD_GET) {
            return parent::call($method, $request);
        }

        $headers = array_merge($this->getHeaders(), $request->getHeaders());
        unset($headers['Content-Type']);
        $request->setHeaders($headers);

        $response = $this->httpClient->request($method, $this->getRequestUrl($request), $request->getHeaders(), '');

        $this->validateResponse($response);

        return $response;
    }
}
