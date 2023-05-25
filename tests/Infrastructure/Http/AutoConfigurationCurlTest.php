<?php

/** @noinspection PhpMissingDocCommentInspection */

namespace SeQura\Core\Tests\Infrastructure\Http;

use SeQura\Core\Infrastructure\Exceptions\BaseException;
use SeQura\Core\Infrastructure\Http\AutoConfiguration;
use SeQura\Core\Infrastructure\Http\CurlHttpClient;
use SeQura\Core\Infrastructure\Http\DTO\Options;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestCurlHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class AutoConfigurationCurlTest extends BaseInfrastructureTestWithServices
{
    /**
     * @var \SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestCurlHttpClient
     */
    protected $httpClient;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new TestCurlHttpClient();
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
        $response = $this->getResponse(200);
        $this->httpClient->setMockResponses(array($response));

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertTrue($success, 'Auto-configure must be successful if default configuration request passed.');
        $this->assertCount(
            0,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should not be called'
        );
        $this->assertEmpty($this->getHttpConfigurationOptions(), 'Additional options should remain empty');
        $this->assertEquals(AutoConfiguration::STATE_SUCCEEDED, $controller->getState());
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureSuccessWithSomeCombination()
    {
        $responses = array(
            $this->getResponse(400),
            $this->getResponse(200),
        );
        $this->httpClient->setMockResponses($responses);
        $additionalOptionsCombination = array(new Options(CurlHttpClient::SWITCH_PROTOCOL, true));

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
            $this->getHttpConfigurationOptions(),
            'Additional options should be set to first combination'
        );
        $setOptions = $this->httpClient->getCurlOptions();
        $this->assertEquals('https://example.com', $setOptions[CURLOPT_URL], 'Protocol for URL should be updated.');
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureSuccessWithAllCombination()
    {
        $responses = array(
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(200),
        );
        $this->httpClient->setMockResponses($responses);
        $additionalOptionsCombination = array(
            new Options(CurlHttpClient::SWITCH_PROTOCOL, true),
            new Options(CURLOPT_FOLLOWLOCATION, false),
            new Options(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6),
        );

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertTrue($success, 'Auto-configure must be successful if request passed with some combination.');
        $this->assertCount(
            7,
            $this->httpClient->setAdditionalOptionsCallHistory['example.com'],
            'Set additional options should be called seven times'
        );
        $this->assertCount(8, $this->httpClient->getHistory(), 'There should be seven calls');
        $this->assertEquals(
            $additionalOptionsCombination,
            $this->getHttpConfigurationOptions(),
            'Additional options should be set to first combination'
        );
        $setOptions = $this->httpClient->getCurlOptions();
        $this->assertEquals('https://example.com', $setOptions[CURLOPT_URL], 'Protocol for URL should be updated.');
    }

    /**
     * Test auto-configure to be successful with some combination options set
     */
    public function testAutoConfigureFailed()
    {
        $responses = array(
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(400),
        );
        $this->httpClient->setMockResponses($responses);

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $success = $controller->start();

        $this->assertFalse($success, 'Auto-configure must failed if no combination resulted with request passed.');
        $this->assertCount(
            14,
            $this->httpClient->setAdditionalOptionsCallHistory['example.com'],
            'Set additional options should be called 14 times'
        );
        $this->assertEmpty(
            $this->getHttpConfigurationOptions(),
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
            14,
            $this->httpClient->setAdditionalOptionsCallHistory['example.com'],
            'Set additional options should be called 14 times'
        );
        $this->assertEmpty(
            $this->getHttpConfigurationOptions(),
            'Reset additional options method should be called and additional options should be empty.'
        );
    }

    /**
     * Tests setting and resetting HTTP options for different domains.
     */
    public function testHttpOptionsForDifferentDomains()
    {
        $responses = array(
            $this->getResponse(400),
            $this->getResponse(200),
        );
        $this->httpClient->setMockResponses($responses);

        $controller = new AutoConfiguration($this->shopConfig, $this->httpClient);
        $controller->start();

        $this->shopConfig->setAutoConfigurationUrl('https://anotherdomain.com/test.php');
        $responses = array(
            $this->getResponse(400),
            $this->getResponse(400),
            $this->getResponse(200),
        );
        $this->httpClient->setMockResponses($responses);
        $controller->start();

        $firstDomainOptions = $this->shopConfig->getHttpConfigurationOptions('example.com');
        $this->assertCount(1, $firstDomainOptions);
        $this->assertEquals(CurlHttpClient::SWITCH_PROTOCOL, $firstDomainOptions[0]->getName());

        $secondDomainOptions = $this->shopConfig->getHttpConfigurationOptions('anotherdomain.com');
        $this->assertCount(1, $secondDomainOptions);
        $this->assertEquals(CURLOPT_FOLLOWLOCATION, $secondDomainOptions[0]->getName());

        $this->assertCount(
            2,
            $this->httpClient->setAdditionalOptionsCallHistory,
            'Set additional options should be called for 2 domains'
        );
    }

    private function getResponse($code)
    {
        // \r is added because HTTP response string from curl has CRLF line separator
        return array(
            'status' => $code,
            'data' => "HTTP/1.1 100 Continue\r
\r
HTTP/1.1 $code OK\r
Cache-Control: no-cache\r
Server: test\r
Date: Wed Jul 4 15:32:03 2019\r
Connection: Keep-Alive:\r
Content-Type: application/json\r
Content-Length: 24860\r
X-Custom-Header: Content: database\r
\r
{\"status\":\"success\"}",
        );
    }

    private function getHttpConfigurationOptions()
    {
        $domain = parse_url($this->shopConfig->getAutoConfigurationUrl(), PHP_URL_HOST);

        return $this->shopConfig->getHttpConfigurationOptions($domain);
    }
}
