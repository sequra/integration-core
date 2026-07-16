<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Affiliate;

use Exception;
use SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests\SendCancellationRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Affiliate\Requests\SendConversionRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\ProxyContracts\AffiliateProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Affiliate\Services\AffiliateSettingsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockAffiliateProxy;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class AffiliateApiTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Affiliate
 */
class AffiliateApiTest extends BaseTestCase
{
    private const STORE = '1';

    /**
     * @var MockAffiliateProxy
     */
    private $mockProxy;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockProxy = new MockAffiliateProxy();
        TestServiceRegister::registerService(AffiliateProxyInterface::class, function () {
            return $this->mockProxy;
        });
    }

    /**
     * A conversion sources the credentials from the stored settings (the request carries only the
     * order data) and forwards them to the proxy.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testConversionSourcesStoredCredentials(): void
    {
        $this->enableAffiliate();

        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportConversion(new SendConversionRequest('merchant1', 'tx-9', 12.5, 'order-77'));

        self::assertTrue($response->isSuccessful());
        self::assertSame(['sent' => true], $response->toArray());

        self::assertCount(1, $this->mockProxy->conversions);
        $conversion = $this->mockProxy->conversions[0];
        self::assertSame('merchant1', $conversion->getMerchantId());
        self::assertSame('offer-1', $conversion->getOfferId());
        self::assertSame('token-123', $conversion->getSecurityToken());
        self::assertSame('tx-9', $conversion->getTransactionId());
        self::assertSame(12.5, $conversion->getAmount());
        self::assertSame('order-77', $conversion->getOrderReference());
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testCancellationSourcesStoredCredentials(): void
    {
        $this->enableAffiliate();

        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportCancellation(new SendCancellationRequest('merchant1', 'tx-9'));

        self::assertSame(['sent' => true], $response->toArray());

        self::assertCount(1, $this->mockProxy->cancellations);
        $cancellation = $this->mockProxy->cancellations[0];
        self::assertSame('merchant1', $cancellation->getMerchantId());
        self::assertSame('offer-1', $cancellation->getOfferId());
        self::assertSame('token-123', $cancellation->getSecurityToken());
        self::assertSame('tx-9', $cancellation->getTransactionId());
    }

    /**
     * When affiliate marketing is disabled for the store nothing is dispatched.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDisabledAffiliateDispatchesNothing(): void
    {
        // No settings seeded -> defaults to disabled.
        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportConversion(new SendConversionRequest('merchant1', 'tx-9', 12.5, 'order-77'));

        self::assertSame(['sent' => false], $response->toArray());
        self::assertCount(0, $this->mockProxy->conversions);

        // A disabled store is a valid state, not an error: the call is successful even though
        // nothing was dispatched. isDispatched()/sent is what tells the two apart (see the error
        // path in testProxyExceptionSurfacesAsErrorResponse).
        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isDispatched());
    }

    /**
     * Enabled credentials with a blank offer id / token are coerced to disabled, so nothing is
     * dispatched (the enabled flag alone must never trigger a postback without credentials).
     *
     * @return void
     *
     * @throws Exception
     */
    public function testEnabledWithoutCredentialsDispatchesNothing(): void
    {
        StoreContext::doWithStore(
            self::STORE,
            [TestServiceRegister::getService(AffiliateSettingsService::class), 'setAffiliateSettings'],
            [new AffiliateSettings(true, '', '')]
        );

        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportCancellation(new SendCancellationRequest('merchant1', 'tx-9'));

        self::assertSame(['sent' => false], $response->toArray());
        self::assertCount(0, $this->mockProxy->cancellations);
    }

    /**
     * When the destination reports the postback as not accepted (proxy returns false), the
     * response reflects it as not dispatched — but it is still a successful, error-free call.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testProxyReturningFalsePropagatesAsNotDispatched(): void
    {
        $this->enableAffiliate();
        $this->mockProxy->setReturn(false);

        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportConversion(new SendConversionRequest('merchant1', 'tx-9', 12.5, 'order-77'));

        self::assertCount(1, $this->mockProxy->conversions);
        self::assertSame(['sent' => false], $response->toArray());
        self::assertFalse($response->isDispatched());
        self::assertTrue($response->isSuccessful());
    }

    /**
     * When the destination rejects the postback (the proxy throws), the facade's error handling
     * turns it into an unsuccessful response — distinct from the disabled path, which stays
     * successful. This is the signal the plugin uses to tell a real failure from a no-op.
     *
     * @return void
     *
     * @throws Exception
     */
    public function testProxyExceptionSurfacesAsErrorResponse(): void
    {
        $this->enableAffiliate();
        $this->mockProxy->setException(new HttpRequestException('destination rejected'));

        $response = CheckoutAPI::get()->affiliate(self::STORE)
            ->reportConversion(new SendConversionRequest('merchant1', 'tx-9', 12.5, 'order-77'));

        self::assertFalse($response->isSuccessful());
    }

    /**
     * @return void
     */
    private function enableAffiliate(): void
    {
        StoreContext::doWithStore(
            self::STORE,
            [TestServiceRegister::getService(AffiliateSettingsService::class), 'setAffiliateSettings'],
            [new AffiliateSettings(true, 'offer-1', 'token-123')]
        );
    }
}
