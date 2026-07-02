<?php

namespace SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Affiliate;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateCancellation;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateConversion;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\SeQuraAPI\Authorization\AuthorizedProxy;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Infrastructure\Http\HttpClient;
use SeQura\Core\Infrastructure\Http\HttpResponse;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\TestHttpClient;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AffiliateProxyTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\SeQuraAPI\Affiliate
 */
class AffiliateProxyTest extends BaseTestCase
{
    /**
     * @var AffiliateProxyInterface
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

        $this->proxy = TestServiceRegister::getService(AffiliateProxyInterface::class);

        $repository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'test',
            'sequra',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $repository->setConnectionData($connectionData);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testConversionMethodIsGet(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendConversion($this->conversion());

        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals(HttpClient::HTTP_METHOD_GET, $this->httpClient->getLastRequest()['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testConversionUrlAndQuery(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendConversion($this->conversion());

        $url = $this->httpClient->getLastRequest()['url'];
        self::assertStringContainsString('aff_lsr', $url);
        self::assertStringContainsString('offer_id=offer-1', $url);
        self::assertStringContainsString('security_token=token-123', $url);
        self::assertStringContainsString('transaction_id=tx-9', $url);
        self::assertStringContainsString('adv_sub=order-77', $url);
        // Amount is normalised to two decimals.
        self::assertStringContainsString('amount=12.50', $url);
    }

    /**
     * The runtime router forwards the request verbatim to a third party, so the seQura Basic Auth
     * header must never be attached (it would leak the connection credentials to the affiliate
     * network). This is the reason the proxy extends BaseProxy and not AuthorizedProxy.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testConversionCarriesNoAuthorizationHeader(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendConversion($this->conversion());

        self::assertArrayNotHasKey(
            AuthorizedProxy::AUTHORIZATION_HEADER_KEY,
            $this->httpClient->getLastRequest()['headers']
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testConversionSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        self::assertTrue($this->proxy->sendConversion($this->conversion()));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationMethodIsPost(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendCancellation($this->cancellation());

        self::assertCount(1, $this->httpClient->getHistory());
        self::assertEquals(HttpClient::HTTP_METHOD_POST, $this->httpClient->getLastRequest()['method']);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationUrl(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendCancellation($this->cancellation());

        self::assertStringContainsString(
            'affiliate_network/webhooks/conversion_status',
            $this->httpClient->getLastRequest()['url']
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationBody(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendCancellation($this->cancellation());

        $body = json_decode($this->httpClient->getLastRequest()['body'], true);
        self::assertSame([
            'transaction_id' => 'tx-9',
            'offer_id' => 'offer-1',
            'security_token' => 'token-123',
            'status' => 'cancelled',
        ], $body);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationCarriesNoAuthorizationHeader(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        $this->proxy->sendCancellation($this->cancellation());

        self::assertArrayNotHasKey(
            AuthorizedProxy::AUTHORIZATION_HEADER_KEY,
            $this->httpClient->getLastRequest()['headers']
        );
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationSuccessfulResponse(): void
    {
        $this->httpClient->setMockResponses([new HttpResponse(200, [], '')]);

        self::assertTrue($this->proxy->sendCancellation($this->cancellation()));
    }

    /**
     * @return AffiliateConversion
     */
    private function conversion(): AffiliateConversion
    {
        return new AffiliateConversion('testId', 'offer-1', 'token-123', 'tx-9', 12.5, 'order-77');
    }

    /**
     * @return AffiliateCancellation
     */
    private function cancellation(): AffiliateCancellation
    {
        return new AffiliateCancellation('testId', 'offer-1', 'token-123', 'tx-9');
    }
}
