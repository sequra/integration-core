<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\Checkout\Services;

use SeQura\Core\BusinessLogic\Domain\Checkout\Services\CheckoutService;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\BadMerchantIdException;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\Product\ProductServiceInterface;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockGeneralSettingsService;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockProductService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class CheckoutValidationServiceTest.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Domain\Checkout\Services
 */
class CheckoutValidationServiceTest extends BaseTestCase
{
    /**
     * @var CheckoutService
     */
    private $checkoutValidationService;

    /**
     * @var MockGeneralSettingsService
     */
    private $mockGeneralSettingsService;

    /**
     * @var MockProductService
     */
    private $mockProductService;

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $connectionService = $this->createMock(ConnectionService::class);
        $connectionService->method('getCredentials')->willReturn([]);
        TestServiceRegister::registerService(ConnectionService::class, static function () use ($connectionService) {
            return $connectionService;
        });

        $this->mockGeneralSettingsService = new MockGeneralSettingsService(
            TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class),
            TestServiceRegister::getService(ConnectionService::class),
            TestServiceRegister::getService(CountryConfigurationService::class)
        );

        $this->mockProductService = new MockProductService();

        TestServiceRegister::registerService(
            ProductServiceInterface::class,
            function () {
                return $this->mockProductService;
            }
        );

        TestServiceRegister::registerService(
            GeneralSettingsService::class,
            function () {
                return $this->mockGeneralSettingsService;
            }
        );

        CheckoutService::$generalSettingsFetched = false;
        CheckoutService::$generalSettings = null;

        $this->checkoutValidationService = TestServiceRegister::getService(CheckoutService::class);
    }

    /**
     * @return void
     */
    public function testCurrencyUnsupported(): void
    {
        self::assertFalse($this->checkoutValidationService->isCurrencySupported('USD'));
    }

    /**
     * @return void
     */
    public function testCurrencySupported(): void
    {
        self::assertTrue($this->checkoutValidationService->isCurrencySupported('EUR'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws WrongCredentialsException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     */
    public function testIpAddressValidWhenNoGeneralSettings(): void
    {
        CheckoutService::$generalSettingsFetched = true;

        self::assertTrue($this->checkoutValidationService->isIpAddressValid('test'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIpAddressValidWhenNoAllowedIPsConfigured(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, null)
        );

        self::assertTrue($this->checkoutValidationService->isIpAddressValid('test'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIpAddressInvalidWhenNotInAllowList(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7'],
                null,
                null
            )
        );

        self::assertFalse($this->checkoutValidationService->isIpAddressValid('test'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIpAddressValidWhenInAllowList(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );

        self::assertTrue($this->checkoutValidationService->isIpAddressValid('test'));
    }

    /**
     * @dataProvider dataProviderIsProductSupportedForVirtualProduct
     *
     * @param ?GeneralSettings $generalSettings
     * @param bool $expected
     *
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductSupportedForVirtualProduct(?GeneralSettings $generalSettings, bool $expected): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings($generalSettings);
        $this->mockProductService->setMockProductSku('sku1');
        $this->mockProductService->setMockProductVirtual(true);

        self::assertEquals($expected, $this->checkoutValidationService->isProductSupported('sku1'));
    }

    /**
     * @return array<array<mixed>>
     */
    public function dataProviderIsProductSupportedForVirtualProduct(): array
    {
        return [
            // No general settings
            [
                null,
                true,
            ],
            // No service selling enabled
            [
                new GeneralSettings(true, null, [], [], []),
                false,
            ],
            // Service selling enabled
            [
                new GeneralSettings(true, null, [], [], [], ['ES']),
                true,
            ],
        ];
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductSupportedNoGeneralSettings(): void
    {
        self::assertTrue($this->checkoutValidationService->isProductSupported('sku1'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductUnsupportedWhenSkuExcluded(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                ['sku1', 'test1'],
                null
            )
        );
        $this->mockProductService->setMockProductSku('sku1');

        self::assertFalse($this->checkoutValidationService->isProductSupported('sku1'));
    }

    /**
     * @dataProvider dataProviderIsProductSupportedEmptySku
     *
     * @param string $sku
     *
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductSupportedEmptySku(string $sku): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, null)
        );
        $this->mockProductService->setMockProductSku('');

        self::assertTrue($this->checkoutValidationService->isProductSupported($sku));
    }

    /**
     * @return array<array<string>>
     */
    public function dataProviderIsProductSupportedEmptySku(): array
    {
        return [[''], ['0']];
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductUnsupportedWhenCategoryExcluded(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                ['sku12', 'test21'],
                ['test1', 'test2', 'test3', 'test4', 'test5']
            )
        );
        $this->mockProductService->setMockProductCategories(['test1', 'test8', 'test2']);

        self::assertFalse($this->checkoutValidationService->isProductSupported('sku1'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductSupportedWhenNoExclusionSet(): void
    {
        $this->mockProductService->setMockProductSku('sku1');
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );

        self::assertTrue($this->checkoutValidationService->isProductSupported('sku1'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsProductSupportedTrue(): void
    {
        $this->mockProductService->setMockProductSku('sku1');
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(
                true,
                null,
                ['testing', 'testing2', 'testing3', 'testing4', 'testing5', 'testing6', 'testing7', 'test'],
                null,
                null
            )
        );

        self::assertTrue($this->checkoutValidationService->isProductSupported('sku1123'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsWidgetSupportedHappyPathWithoutProduct(): void
    {
        self::assertTrue($this->checkoutValidationService->isWidgetSupported('EUR', '1.2.3.4'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsWidgetSupportedHappyPathWithProduct(): void
    {
        $this->mockProductService->setMockProductSku('sku1');

        self::assertTrue($this->checkoutValidationService->isWidgetSupported('EUR', '1.2.3.4', 'sku1'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsWidgetSupportedFailsOnCurrency(): void
    {
        self::assertFalse($this->checkoutValidationService->isWidgetSupported('USD', '1.2.3.4'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsWidgetSupportedFailsOnIp(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, ['9.9.9.9'], null, null)
        );

        self::assertFalse($this->checkoutValidationService->isWidgetSupported('EUR', '1.2.3.4'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsWidgetSupportedFailsOnExcludedProduct(): void
    {
        $this->mockProductService->setMockProductSku('sku-excluded');
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, ['sku-excluded'], null)
        );

        self::assertFalse($this->checkoutValidationService->isWidgetSupported('EUR', '1.2.3.4', 'sku-excluded'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedHappyPath(): void
    {
        $this->mockProductService->setMockProductSku('sku1');

        self::assertTrue(
            $this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', ['sku1', 'sku2'])
        );
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedHappyPathWithEmptyProducts(): void
    {
        self::assertTrue($this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', []));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedFailsOnCurrency(): void
    {
        self::assertFalse($this->checkoutValidationService->isExpressCheckoutSupported('USD', '1.2.3.4', []));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedFailsOnIp(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, ['9.9.9.9'], null, null)
        );

        self::assertFalse($this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', []));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedFailsOnExcludedProductInCart(): void
    {
        $this->mockProductService->setMockProductSku('sku-excluded');
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, ['sku-excluded'], null)
        );

        self::assertFalse(
            $this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', ['ok', 'sku-excluded'])
        );
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedFailsOnExcludedCategoryInCart(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, ['cat-excluded'])
        );

        self::assertFalse(
            $this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', [], ['ok', 'cat-excluded'])
        );
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsExpressCheckoutSupportedHappyPathWithCategories(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, ['other-category'])
        );

        self::assertTrue(
            $this->checkoutValidationService->isExpressCheckoutSupported('EUR', '1.2.3.4', [], ['cat1', 'cat2'])
        );
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsCategorySupportedEmptyCategory(): void
    {
        self::assertTrue($this->checkoutValidationService->isCategorySupported(''));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsCategorySupportedNoGeneralSettings(): void
    {
        self::assertTrue($this->checkoutValidationService->isCategorySupported('cat1'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsCategoryUnsupportedWhenExcluded(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, ['cat-excluded'])
        );

        self::assertFalse($this->checkoutValidationService->isCategorySupported('cat-excluded'));
    }

    /**
     * @return void
     *
     * @throws BadMerchantIdException
     * @throws FailedToRetrieveSellingCountriesException
     * @throws HttpRequestException
     * @throws WrongCredentialsException
     */
    public function testIsCategorySupportedWhenNotExcluded(): void
    {
        $this->mockGeneralSettingsService->saveGeneralSettings(
            new GeneralSettings(true, null, null, null, ['cat-excluded'])
        );

        self::assertTrue($this->checkoutValidationService->isCategorySupported('cat1'));
    }
}
