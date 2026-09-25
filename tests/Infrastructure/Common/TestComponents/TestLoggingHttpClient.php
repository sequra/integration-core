<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents;

use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\Http\LoggingHttpclient;

/**
 * Class TestLoggingHttpClient
 *
 * Exposes the request methods the base class keeps protected, so the delegation to the
 * wrapped client can be asserted.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common\TestComponents
 */
class TestLoggingHttpClient extends LoggingHttpclient
{
    /**
     * @inheritdoc
     */
    public function sendHttpRequest(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): HttpResponse {
        return parent::sendHttpRequest($method, $url, $headers, $body);
    }

    /**
     * @inheritdoc
     */
    public function sendHttpRequestAsync(
        string $method,
        string $url,
        ?array $headers = array(),
        string $body = ''
    ): void {
        parent::sendHttpRequestAsync($method, $url, $headers, $body);
    }
}
