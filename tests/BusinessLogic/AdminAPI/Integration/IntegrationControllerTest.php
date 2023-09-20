<?php

namespace SeQura\Core\Tests\BusinessLogic\AdminAPI\Integration;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationShopNameResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationUIStateResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Integration\Responses\IntegrationVersionResponse;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Models\CountryConfiguration;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Version\Exceptions\FailedToRetrieveVersionException;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\BusinessLogic\Common\MockComponents\MockVersionService;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

/**
 * Class IntegrationControllerTest
 *
 * @package SeQura\Core\Tests\BusinessLogic\AdminAPI\Integration
 */
class IntegrationControllerTest extends BaseTestCase
{
    /**
     * @var ConnectionDataRepositoryInterface
     */
    private $connectionDataRepository;

    /**
     * @var CountryConfigurationRepositoryInterface
     */
    private $countryConfigurationRepository;

    /**
     * @var WidgetSettingsRepositoryInterface
     */
    protected $widgetSettingsRepository;

    public function setUp(): void
    {
        parent::setUp();

        TestServiceRegister::registerService(VersionServiceInterface::class, static function () {
            return new MockVersionService();
        });

        $this->connectionDataRepository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
        $this->countryConfigurationRepository = TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class);
        $this->widgetSettingsRepository = TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class);
    }

    public function testIsGetUIStateResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testGetUIStateResponse(): void
    {
        // Arrange
        $expectedResponse = IntegrationUIStateResponse::connection();

        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    /**
     * @throws Exception
     */
    public function testGetUIStateDashboardResponseToArray(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        $countryConfigurations = [
            new CountryConfiguration('CO','logeecom'),
            new CountryConfiguration('ES','logeecom'),
            new CountryConfiguration('FR','logeecom')
        ];

        $widgetSettings = new WidgetSettings(
            true,
            'test123',
            true,
            true,
            true,
            '.test'
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);
        StoreContext::doWithStore('1', [$this->countryConfigurationRepository,'setCountryConfiguration'], [$countryConfigurations]);
        StoreContext::doWithStore('1', [$this->widgetSettingsRepository,'setWidgetSettings'], [$widgetSettings]);

        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertEquals(['state' => 'dashboard'], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetUIStateNoConnectionDataResponseToArray(): void
    {
        // Arrange
        $countryConfigurations = [
            new CountryConfiguration('CO','logeecom'),
            new CountryConfiguration('ES','logeecom'),
            new CountryConfiguration('FR','logeecom')
        ];

        StoreContext::doWithStore('1', [$this->countryConfigurationRepository,'setCountryConfiguration'], [$countryConfigurations]);

        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertEquals(['state' => 'connection'], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetUIStateNoCountryConfigurationResponseToArray(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);

        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertEquals(['state' => 'country_configuration'], $response->toArray());
    }

    /**
     * @throws Exception
     */
    public function testGetUIStateNoWidgetConfigurationResponseToArray(): void
    {
        // Arrange
        $connectionData = new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            new AuthorizationCredentials('test_username', 'test_password')
        );
        $countryConfigurationData = [new CountryConfiguration('ES', 'test')];

        StoreContext::doWithStore('1', [$this->connectionDataRepository,'setConnectionData'], [$connectionData]);
        StoreContext::doWithStore('1', [$this->countryConfigurationRepository,'setCountryConfiguration'], [$countryConfigurationData]);

        // Act
        $response = AdminAPI::get()->integration('1')->getUIState();

        // Assert
        self::assertEquals(['state' => 'widget_configuration'], $response->toArray());
    }

    /**
     * @throws FailedToRetrieveVersionException
     */
    public function testIsGetVersionResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->integration('1')->getVersion();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    /**
     * @throws FailedToRetrieveVersionException
     */
    public function testGetVersionResponse(): void
    {
        // Arrange
        $expectedResponse = new IntegrationVersionResponse(new Version('v1.0.1','v1.0.3','test'));

        // Act
        $response = AdminAPI::get()->integration('1')->getVersion();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }


    /**
     * @throws FailedToRetrieveVersionException
     */
    public function testGetVersionResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->integration('1')->getVersion();

        // Assert
        self::assertEquals($this->expectedVersionToArrayResponse(), $response->toArray());
    }

    public function testIsGetShopNameResponseSuccessful(): void
    {
        // Act
        $response = AdminAPI::get()->integration('1')->getShopName();

        // Assert
        self::assertTrue($response->isSuccessful());
    }

    public function testGetShopNameResponse(): void
    {
        // Arrange
        $expectedResponse = new IntegrationShopNameResponse('Test');

        // Act
        $response = AdminAPI::get()->integration('1')->getShopName();

        // Assert
        self::assertEquals($expectedResponse, $response);
    }

    public function testGetShopNameResponseToArray(): void
    {
        // Act
        $response = AdminAPI::get()->integration('1')->getShopName();

        // Assert
        self::assertEquals(['shopName' => 'Test'], $response->toArray());
    }

    private function expectedVersionToArrayResponse(): array
    {
        return [
            'current' => 'v1.0.1',
            'new' => 'v1.0.3',
            'downloadNewVersionUrl' => 'test'
        ];
    }
}
