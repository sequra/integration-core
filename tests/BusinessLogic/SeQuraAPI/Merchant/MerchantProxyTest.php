<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Merchant;

use DateTime;
use Exception;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Merchant\Models\GetAvailablePaymentMethodsRequest;
use SeQura\Core\BusinessLogic\Domain\Merchant\ProxyContracts\MerchantProxyInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiInvalidUrlParameterException;
use SeQura\Core\BusinessLogic\SeQuraAPI\Exceptions\HttpApiUnauthorizedException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class MerchantProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Merchant
 */
class MerchantProxyTest extends BaseTestCase
{
    /**
     * @var MerchantProxyInterface
     */
    public $proxy;
    /**
     * @var TestHttpClient
     */
    public $httpClient;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     * @throws InvalidEnvironmentException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = TestServiceRegister::getService(HttpClient::class);
        $this->httpClient = $httpClient;
        TestServiceRegister::registerService(HttpClient::class, function () {
            return $this->httpClient;
        });

        $this->proxy = TestServiceRegister::getService(MerchantProxyInterface::class);

        $repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);

        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $repository->setConnectionData($connectionData);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsUrl(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertStringContainsString('merchants/testId/payment_methods', $lastRequest['url']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsAuthHeader(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertArrayHasKey('Authorization', $lastRequest['headers']);
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsMethod(): void
    {
        $this->httpClient->setMockResponses([
            new HttpResponse(200, [], file_get_contents(
                __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
            ))
        ]);

        $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));
        self::assertCount(1, $this->httpClient->getHistory());
        $lastRequest = $this->httpClient->getLastRequest();
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $lastRequest['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testGetPaymentMethodsSuccessfulResponse(): void
    {
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/SuccessfulResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(200, [], $rawResponseBody)]);
        $response = $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('testId'));
        $responseBody = json_decode($rawResponseBody, true);
        $paymentMethods = [];

        foreach ($responseBody['payment_options'] as $option) {
            foreach ($option['methods'] as $method) {
                $paymentMethods[] = $method;
            }
        }

        for ($i = 0, $iMax = count($paymentMethods); $i < $iMax; $i++) {
            self::assertEquals($paymentMethods[$i]['product'], $response[$i]->getProduct());
            self::assertEquals($paymentMethods[$i]['campaign'], $response[$i]->getCampaign());
            self::assertEquals($paymentMethods[$i]['title'], $response[$i]->getTitle());
            self::assertEquals($paymentMethods[$i]['long_title'], $response[$i]->getLongTitle());
            self::assertEquals($paymentMethods[$i]['claim'], $response[$i]->getClaim());
            self::assertEquals($paymentMethods[$i]['description'], $response[$i]->getDescription());
            self::assertEquals($paymentMethods[$i]['icon'], $response[$i]->getIcon());
            self::assertEquals(new DateTime($paymentMethods[$i]['starts_at']), $response[$i]->getStartsAt());
            self::assertEquals(new DateTime($paymentMethods[$i]['ends_at']), $response[$i]->getEndsAt());
            self::assertEquals($paymentMethods[$i]['min_amount'], $response[$i]->getMinAmount());
            self::assertEquals($paymentMethods[$i]['max_amount'], $response[$i]->getMaxAmount());
            self::assertEquals($paymentMethods[$i]['cost_description'], $response[$i]->getCostDescription());
            self::assertEquals($paymentMethods[$i]['cost']['setup_fee'], $response[$i]->getCost()->getSetupFee());
            self::assertEquals($paymentMethods[$i]['cost']['instalment_fee'], $response[$i]->getCost()->getInstalmentFee());
            self::assertEquals($paymentMethods[$i]['cost']['down_payment_fees'], $response[$i]->getCost()->getDownPaymentFees());
            self::assertEquals($paymentMethods[$i]['cost']['instalment_total'], $response[$i]->getCost()->getInstalmentTotal());
        }
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsInvalidMerchantIdResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(
            __DIR__ . '/../../Common/ApiResponses/Merchant/GetPaymentMethodsResponses/InvalidMerchantIdResponse.json'
        );

        $this->httpClient->setMockResponses([new HttpResponse(403, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiInvalidUrlParameterException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Access forbidden.', $exception->getMessage());
        self::assertEquals(403, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }

    /**
     * @return void
     *
     * @throws HttpRequestException
     */
    public function testGetPaymentMethodsUnauthorizedResponse(): void
    {
        $exception = null;
        $rawResponseBody = file_get_contents(__DIR__ . '/../../Common/ApiResponses/InvalidCredentialsResponse.txt');
        $this->httpClient->setMockResponses([new HttpResponse(401, [], $rawResponseBody)]);

        try {
            $this->proxy->getAvailablePaymentMethods(new GetAvailablePaymentMethodsRequest('test'));
        } catch (HttpApiUnauthorizedException $exception) {
        }

        $responseBody = json_decode($rawResponseBody, true);
        self::assertNotNull($exception);
        self::assertEquals('Wrong credentials.', $exception->getMessage());
        self::assertEquals(401, $exception->getCode());
        self::assertEquals($responseBody['errors'] ?? [], $exception->getErrors());
    }
}
