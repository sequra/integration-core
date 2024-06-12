<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents;

use SeQura\Core\Infrastructure\Http\AsyncSocketHttpClient;

class TestAsyncSocketHttpClient extends AsyncSocketHttpClient
{
    public $requestHistory = array();

    protected function executeRequest(
        string $transferProtocol,
        string $host,
        int $port,
        int $timeOut,
        string $payload
    ): void {
        $this->requestHistory[] = array(
            'transferProtocol' => $transferProtocol,
            'host' => $host,
            'port' => $port,
            'timeout' => $timeOut,
            'payload' => $payload,
        );
    }
}
