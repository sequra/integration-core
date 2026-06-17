<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Checkout\Services;

use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutInitializationService;
use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\CredentialsService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetConfiguratorInterface;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;

/**
 * Class CheckoutInitializationServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Checkout\Services
 */
class CheckoutInitializationServiceTest extends BaseTestCase
{
    /**
     * @var CredentialsService
     */
    private $credentialsService;
    /**
     * @var CheckoutService
     */
    private $checkoutService;
    /**
     * @var WidgetConfiguratorInterface
     */
    private $widgetConfigurator;
    /**
     * @var PaymentMethodsService
     */
    private $paymentMethodsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentialsService = $this->createMock(CredentialsService::class);
        $this->checkoutService = $this->createMock(CheckoutService::class);
        $this->widgetConfigurator = $this->createMock(WidgetConfiguratorInterface::class);
        $this->paymentMethodsService = $this->createMock(PaymentMethodsService::class);
    }

    /**
     * @return CheckoutInitializationService
     */
    private function service(): CheckoutInitializationService
    {
        return new CheckoutInitializationService(
            $this->credentialsService,
            $this->checkoutService,
            $this->widgetConfigurator,
            $this->paymentMethodsService
        );
    }

    /**
     * Builds the full bootstrap config from the resolved credentials and configurator.
     */
    public function testReturnsInitializationDataWhenCredentialsExist(): void
    {
        // Arrange
        $credentials = $this->createMock(Credentials::class);
        $credentials->method('getMerchantId')->willReturn('merchant1');
        $credentials->method('getAssetsKey')->willReturn('assets1');
        $credentials->method('getDeployment')->willReturn('sequra');

        $this->credentialsService->method('getCredentialsByCountry')->with('ES', 'ES')->willReturn($credentials);
        $this->paymentMethodsService->method('getMerchantPromotionalProducts')->with('merchant1')
            ->willReturn(['i1', 'pp3']);
        $this->checkoutService->method('getScriptUri')->with('sequra')
            ->willReturn('https://live.sequracdn.com/assets/sequra-checkout.min.js');
        $this->widgetConfigurator->method('getLocale')->willReturn('es-ES');
        $this->widgetConfigurator->method('getCurrency')->willReturn('EUR');
        $this->widgetConfigurator->method('getDecimalSeparator')->willReturn(',');
        $this->widgetConfigurator->method('getThousandsSeparator')->willReturn('.');

        // Act
        $data = $this->service()->getInitializationData('ES', 'ES');

        // Assert
        self::assertNotNull($data);
        self::assertSame('assets1', $data->getAssetKey());
        self::assertSame('merchant1', $data->getMerchantId());
        self::assertSame(['i1', 'pp3'], $data->getProducts());
        self::assertSame('https://live.sequracdn.com/assets/sequra-checkout.min.js', $data->getScriptUri());
        self::assertSame('es-ES', $data->getLocale());
        self::assertSame('EUR', $data->getCurrency());
        self::assertSame(',', $data->getDecimalSeparator());
        self::assertSame('.', $data->getThousandSeparator());
    }

    /**
     * Returns data with empty identity and resolved-from-empty-deployment script when no
     * credentials match the shopper's country (parity with the previous widget-init behavior).
     * The products lookup must be skipped so it is never queried with an empty merchant id.
     */
    public function testReturnsEmptyIdentityWhenNoCredentials(): void
    {
        // Arrange
        $this->credentialsService->method('getCredentialsByCountry')->willReturn(null);
        $this->paymentMethodsService->expects(self::never())->method('getMerchantPromotionalProducts');
        $this->checkoutService->method('getScriptUri')->with('')->willReturn('');
        $this->widgetConfigurator->method('getLocale')->willReturn('es-ES');
        $this->widgetConfigurator->method('getCurrency')->willReturn('EUR');
        $this->widgetConfigurator->method('getDecimalSeparator')->willReturn(',');
        $this->widgetConfigurator->method('getThousandsSeparator')->willReturn('.');

        // Act
        $data = $this->service()->getInitializationData('ES', 'ES');

        // Assert
        self::assertNotNull($data);
        self::assertSame('', $data->getAssetKey());
        self::assertSame('', $data->getMerchantId());
        self::assertSame([], $data->getProducts());
        self::assertSame('', $data->getScriptUri());
    }

    /**
     * Falls back to default locale formatting when the configurator returns null.
     */
    public function testFallsBackToDefaultFormatting(): void
    {
        // Arrange
        $this->credentialsService->method('getCredentialsByCountry')->willReturn(null);
        $this->paymentMethodsService->method('getMerchantPromotionalProducts')->willReturn([]);
        $this->checkoutService->method('getScriptUri')->willReturn('');
        $this->widgetConfigurator->method('getLocale')->willReturn(null);
        $this->widgetConfigurator->method('getCurrency')->willReturn(null);
        $this->widgetConfigurator->method('getDecimalSeparator')->willReturn(null);
        $this->widgetConfigurator->method('getThousandsSeparator')->willReturn(null);

        // Act
        $data = $this->service()->getInitializationData('ES', 'ES');

        // Assert
        self::assertNotNull($data);
        self::assertSame('es-ES', $data->getLocale());
        self::assertSame('EUR', $data->getCurrency());
        self::assertSame(',', $data->getDecimalSeparator());
        self::assertSame('.', $data->getThousandSeparator());
    }
}
