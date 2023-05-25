<?php

namespace SeQura\Core\Tests\Infrastructure\Http;

use SeQura\Core\Infrastructure\Exceptions\BaseException;
use SeQura\Core\Infrastructure\Http\AutoConfiguration;
use SeQura\Core\Infrastructure\Http\DTO\Options;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class AutoConfigurationTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var TestHttpClient
     */
    protected $httpClient;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestHttpClient();
        $me = $this;
        TestServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () use ($me) {
                return $me->httpClient;
            }
        );

        $this->shopConfig->setAutoConfigurationUrl('http://example.com');
    }

    /**
     * Test auto-configure to throw exception if auto-configure URL is not set.
     */
    public function testAutoConfigureNoUrlSet()
    {
        $this->expectException(BaseException::class);

        $this->shopConfig->setAutoConfigurationUrl(null);
        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $controller->start();
    }

    /**
     * Test auto-configure to be successful with default options
     */
    public function testAutoConfigureSuccessfullyWithDefaultOptions()
    {
        $response = new HttpResponse(200, array(), '{}');
        $this->httpClient->setMockResponses(array($response));

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertTrue($success, 'Auto-configure must be successful if default configuration request passed.');
        $this->assertCount(
            0,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should not be called'
        );
        $this->assertEmpty($this->httpClient->additionalOptions, 'Additional options should remain empty');
        $this->assertEquals(AutoConfiguration::STATE_SUCCEEDED, $controller->getState());
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureSuccessfullyWithSomeCombination()
    {
        $responses = array(
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(200, array(), '{}'),
        );
        $this->httpClient->setMockResponses($responses);
        $additionalOptionsCombination = array(new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4));

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertTrue($success, 'Auto-configure must be successful if request passed with some combination.');
        $this->assertCount(
            1,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called once'
        );
        $this->assertEquals(
            $additionalOptionsCombination,
            $this->httpClient->additionalOptions,
            'Additional options should be set to first combination'
        );
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureFailed()
    {
        $responses = array(
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(400, array(), '{}'),
        );
        $this->httpClient->setMockResponses($responses);

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertFalse($success, 'Auto-configure must failed if no combination resulted with request passed.');
        $this->assertCount(
            4,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called twice'
        );
        $this->assertEmpty(
            $this->httpClient->additionalOptions,
            'Reset additional options method should be called and additional options should be empty.'
        );
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureFailedWhenThereAreNoResponses()
    {
        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertFalse($success, 'Auto-configure must failed if no combination resulted with request passed.');
        $this->assertCount(
            4,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called 4 times'
        );
        $this->assertEmpty(
            $this->httpClient->additionalOptions,
            'Reset additional options method should be called and additional options should be empty.'
        );
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigurePassesWithGet()
    {
        $responses = array(
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(400, array(), '{}'),
            new HttpResponse(200, array(), '{}'),
        );
        $this->httpClient->setMockResponses($responses);

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertTrue($success, 'Auto-configure must pass for GET request if POST failed.');
        $this->assertEmpty(
            $this->httpClient->additionalOptions,
            'Reset additional options method should be called and additional options should be empty.'
        );
        $this->assertEquals(
            HttpClient::HTTP_METHOD_GET,
            $this->shopConfig->getAsyncProcessCallHttpMethod(),
            'Second call should be GET.'
        );
    }
}
