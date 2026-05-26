<?php

namespace SeQura\Core\Tests\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller;

use SeQura\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutAvailabilityRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\ExpressCheckout\Requests\ExpressCheckoutSolicitRequest;
use SeQura\Core\BusinessLogic\CheckoutAPI\Solicitation\Response\IdentificationFormResponse;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraForm;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Service\OrderService;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\CheckoutAPI\Solicitation\MockComponents\MockCreateOrderRequestBuilder;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockExpressCheckoutService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class ExpressCheckoutControllerTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\CheckoutAPI\ExpressCheckout\Controller
 */
class ExpressCheckoutControllerTest extends BaseTestCase
{
    /**
     * @var MockExpressCheckoutService
     */
    private $expressCheckoutService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->expressCheckoutService = new MockExpressCheckoutService(
            TestServiceRegister::getService(ExpressCheckoutSettingsRepositoryInterface::class),
            TestServiceRegister::getService(CheckoutService::class),
            TestServiceRegister::getService(CountryConfigurationService::class),
            TestServiceRegister::getService(PaymentMethodsService::class),
            TestServiceRegister::getService(OrderService::class)
        );

        TestServiceRegister::registerService(ExpressCheckoutService::class, function () {
            return $this->expressCheckoutService;
        });
    }

    /**
     * @return void
     */
    public function testIsAvailableWrapsTrueResult(): void
    {
        $this->expressCheckoutService->setAvailability(true);

        $response = CheckoutAPI::get()->expressCheckout('1')->isAvailable($this->buildRequest());

        self::assertTrue($response->isSuccessful());
        self::assertSame(['available' => true], $response->toArray());
    }

    /**
     * @return void
     */
    public function testIsAvailableWrapsFalseResult(): void
    {
        $this->expressCheckoutService->setAvailability(false);

        $response = CheckoutAPI::get()->expressCheckout('1')->isAvailable($this->buildRequest());

        self::assertSame(['available' => false], $response->toArray());
    }

    /**
     * @return void
     */
    public function testSolicitWrapsFormInIdentificationFormResponse(): void
    {
        $expectedHtml = '<html>express-checkout-form</html>';
        $this->expressCheckoutService->setNextFormResult(new SeQuraForm($expectedHtml));

        $response = CheckoutAPI::get()
            ->expressCheckout('1')
            ->solicit(new ExpressCheckoutSolicitRequest(new MockCreateOrderRequestBuilder()));

        self::assertInstanceOf(IdentificationFormResponse::class, $response);
        self::assertTrue($response->isSuccessful());
        self::assertSame(['identificationForm' => $expectedHtml], $response->toArray());
    }

    /**
     * @return void
     */
    public function testSolicitDelegatesBuilderToService(): void
    {
        $builder = new MockCreateOrderRequestBuilder();

        CheckoutAPI::get()
            ->expressCheckout('1')
            ->solicit(new ExpressCheckoutSolicitRequest($builder));

        self::assertSame($builder, $this->expressCheckoutService->getLastSolicitBuilder());
    }

    /**
     * @return ExpressCheckoutAvailabilityRequest
     */
    private function buildRequest(): ExpressCheckoutAvailabilityRequest
    {
        return new ExpressCheckoutAvailabilityRequest('product', 'ES', 'EUR', '1.2.3.4');
    }
}
