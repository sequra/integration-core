<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\GeneralSettings\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Services\CountryConfigurationService;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Services\GeneralSettingsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Covers what the service answers for a store with and without stored general settings,
 * and how the service lists are derived from the credentials.
 */
class GeneralSettingsServiceTest extends BaseTestCase
{
    /**
     * @var GeneralSettingsRepositoryInterface
     */
    private $repository;

    /**
     * @var ConnectionService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionService;

    /**
     * @var CountryConfigurationService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $countryConfigurationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = TestServiceRegister::getService(GeneralSettingsRepositoryInterface::class);
        $this->connectionService = $this->createMock(ConnectionService::class);
        $this->countryConfigurationService = $this->createMock(CountryConfigurationService::class);
    }

    public function testAnswersTheDefaultsWhenNothingIsStored(): void
    {
        // Arrange
        $this->connectionService->method('getCredentials')->willReturn([]);
        $this->countryConfigurationService->method('getCountryConfiguration')->willReturn(null);

        // Act
        $settings = $this->getGeneralSettings();

        // Assert
        self::assertFalse($settings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertFalse($settings->isShowSeQuraCheckoutAsHostedPage());
        self::assertEquals([], $settings->getAllowedIPAddresses());
        self::assertEquals([], $settings->getExcludedProducts());
        self::assertEquals([], $settings->getExcludedCategories());
        self::assertEquals([], $settings->getEnabledForServices());
        self::assertEquals([], $settings->getAllowFirstServicePaymentDelay());
        self::assertEquals([], $settings->getAllowServiceRegistrationItems());
        self::assertEquals(GeneralSettings::DEFAULT_SERVICE_END_DATE, $settings->getDefaultServicesEndDate());
        self::assertNull($settings->getOrderIdentifier());
    }

    public function testDerivesTheServiceListsFromTheCredentialsWhenNothingIsStored(): void
    {
        // Arrange
        $this->connectionService->method('getCredentials')->willReturn([
            $this->credentials('merchant_es', 'ES', ['services', 'allow_first_instalment_delay', 'with_registration']),
            $this->credentials('merchant_fr', 'FR', ['services']),
            $this->credentials('merchant_pt', 'PT', []),
        ]);
        $this->countryConfigurationService->method('getCountryConfiguration')->willReturn([
            new CountryConfiguration('ES', 'merchant_es'),
            new CountryConfiguration('FR', 'merchant_fr'),
            new CountryConfiguration('PT', 'merchant_pt'),
        ]);

        // Act
        $settings = $this->getGeneralSettings();

        // Assert
        self::assertEquals(['ES', 'FR'], $settings->getEnabledForServices());
        self::assertEquals(['ES'], $settings->getAllowFirstServicePaymentDelay());
        self::assertEquals(['ES'], $settings->getAllowServiceRegistrationItems());
    }

    public function testKeepsTheStoredSettingsAndDerivesTheServiceLists(): void
    {
        // Arrange
        StoreContext::doWithStore('1', [$this->repository, 'setGeneralSettings'], [
            new GeneralSettings(true, true, ['127.0.0.1'], ['1'], ['2'], [], [], [], 'P6M'),
        ]);
        $this->connectionService->method('getCredentials')->willReturn([
            $this->credentials('merchant_es', 'ES', ['services', 'with_registration']),
        ]);
        $this->countryConfigurationService->method('getCountryConfiguration')->willReturn([
            new CountryConfiguration('ES', 'merchant_es'),
        ]);

        // Act
        $settings = $this->getGeneralSettings();

        // Assert
        self::assertTrue($settings->isSendOrderReportsPeriodicallyToSeQura());
        self::assertTrue($settings->isShowSeQuraCheckoutAsHostedPage());
        self::assertEquals(['127.0.0.1'], $settings->getAllowedIPAddresses());
        self::assertEquals(['1'], $settings->getExcludedProducts());
        self::assertEquals(['2'], $settings->getExcludedCategories());
        self::assertEquals('P6M', $settings->getDefaultServicesEndDate());
        self::assertEquals(['ES'], $settings->getEnabledForServices());
        self::assertEquals([], $settings->getAllowFirstServicePaymentDelay());
        self::assertEquals(['ES'], $settings->getAllowServiceRegistrationItems());
    }

    public function testIgnoresTheCredentialsOfAMerchantTheCountryConfigurationDoesNotName(): void
    {
        // Arrange
        $this->connectionService->method('getCredentials')->willReturn([
            $this->credentials('merchant_es', 'ES', ['services']),
            $this->credentials('merchant_es_old', 'ES', ['services', 'with_registration']),
        ]);
        $this->countryConfigurationService->method('getCountryConfiguration')->willReturn([
            new CountryConfiguration('ES', 'merchant_es'),
        ]);

        // Act
        $settings = $this->getGeneralSettings();

        // Assert
        self::assertEquals(['ES'], $settings->getEnabledForServices());
        self::assertEquals([], $settings->getAllowServiceRegistrationItems());
    }

    public function testDerivesNoServiceListWithoutACountryConfiguration(): void
    {
        // Arrange
        $this->connectionService->method('getCredentials')->willReturn([
            $this->credentials('merchant_es', 'ES', ['services']),
        ]);
        $this->countryConfigurationService->method('getCountryConfiguration')->willReturn(null);

        // Act
        $settings = $this->getGeneralSettings();

        // Assert
        self::assertEquals([], $settings->getEnabledForServices());
    }

    /**
     * @return GeneralSettings
     */
    private function getGeneralSettings(): GeneralSettings
    {
        $service = new GeneralSettingsService(
            $this->repository,
            $this->connectionService,
            $this->countryConfigurationService
        );

        return StoreContext::doWithStore('1', [$service, 'getGeneralSettings']);
    }

    /**
     * @param string $merchantId
     * @param string $country
     * @param string[] $contractOptions
     *
     * @return Credentials
     */
    private function credentials(string $merchantId, string $country, array $contractOptions): Credentials
    {
        return new Credentials(
            $merchantId,
            $country,
            'EUR',
            'assets-key',
            ['contract_options' => $contractOptions],
            'sequra'
        );
    }
}
