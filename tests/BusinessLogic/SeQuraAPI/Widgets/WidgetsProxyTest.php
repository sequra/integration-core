<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Widgets;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\ValidateAssetsKeyRequest;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\ProxyContracts\WidgetsProxyInterface;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class WidgetsProxyTest
 *
 * @package BusinessLogic\SeQuraAPI\Widgets
 */
class WidgetsProxyTest extends BaseTestCase
{
    /**
     * @var TestHttpClient
     */
    public $httpClient;
    /**
     * @var WidgetsProxyInterface
     */
    public $proxy;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = TestServiceRegister::getService(WidgetsProxyInterface::class);
    }

    /**
     * @throws Exception
     */
    public function testValidateAssetsKeyUrl(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/Widgets/validateSuccessfulResponse.json')
                )
            ]
        );

        // act
        $this->proxy->validateAssetsKey(new ValidateAssetsKeyRequest('test', ['pp3', 'pp5', 'i1'], '1234567', 'sandbox'));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals('https://sandbox.sequracdn.com/scripts/test/1234567/pp3_pp5_cost.json', $lastRequest['url']);
    }

    /**
     * @throws Exception
     */
    public function testValidateAssetsKeyMethod(): void
    {
        // arrange
        $this->httpClient->setMockResponses(
            [
                new HttpResponse(
                    200,
                    [],
                    file_get_contents(__DIR__ . '/../../Common/ApiResponses/Widgets/validateSuccessfulResponse.json')
                )
            ]
        );

        // act
        $this->proxy->validateAssetsKey(new ValidateAssetsKeyRequest('test', ['pp3', 'pp5', 'i1'], '1234567', 'sandbox'));

        // assert
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }
}
