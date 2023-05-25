<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\MockComponents;

use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\HttpRequest;
use SeQura\Core\Infrastructure\Http\HttpResponse;

/**
 * Class MockBaseProxy
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\MockComponents
 */
class MockBaseProxy extends BaseProxy
{
    /**
     * Base URL that will be used for initializing all HTTP requests
     */
    protected const BASE_API_URL = 'test-sequra-proxy-url.domain.com/test-path';

    public function get(HttpRequest $request): HttpResponse
    {
        return parent::get($request);
    }

    public function delete(HttpRequest $request): HttpResponse
    {
        return parent::delete($request);
    }

    public function put(HttpRequest $request): HttpResponse
    {
        return parent::put($request);
    }

    public function post(HttpRequest $request): HttpResponse
    {
        return parent::post($request);
    }

    public function patch(HttpRequest $request): HttpResponse
    {
        return parent::patch($request);
    }
}
