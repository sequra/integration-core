<?php

namespace SeQura\Core\Tests\BusinessLogic\Domain\UIState\Services;

use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\WrongCredentialsException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\AuthorizationCredentials;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\UIState\Services\UIStateService;
use SeQura\Core\BusinessLogic\SeQuraAPI\BaseProxy;
use SeQura\Core\Tests\BusinessLogic\Common\BaseTestCase;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;

class UIStateServiceTest extends BaseTestCase
{
    /**
     * @var ConnectionService&MockObject
     */
    private $connectionService;

    /**
     * @var ConnectionDataRepositoryInterface
     */
    private $connectionDataRepository;

    /**
     * @var WidgetSettingsRepositoryInterface
     */
    private $widgetSettingsRepository;

    /**
     * @var UIStateService
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionService = $this->createMock(ConnectionService::class);
        $this->connectionDataRepository = TestServiceRegister::getService(ConnectionDataRepositoryInterface::class);
        $this->widgetSettingsRepository = TestServiceRegister::getService(WidgetSettingsRepositoryInterface::class);

        $this->service = new UIStateService(
            $this->connectionService,
            $this->connectionDataRepository,
            TestServiceRegister::getService(CountryConfigurationRepositoryInterface::class),
            $this->widgetSettingsRepository
        );
    }

    /**
     * @throws Exception
     */
    public function testIsOnboardingWithoutConnectionsAndRefreshesNothing(): void
    {
        // Arrange
        $this->connectionService->expects(self::never())->method('refreshCredentials');

        // Act
        $isOnboarding = $this->service->isOnboardingState();

        // Assert
        self::assertTrue($isOnboarding);
    }

    /**
     * @throws Exception
     */
    public function testRefreshesTheCredentialsOfEveryConnectionWithoutWidgetSettings(): void
    {
        // Arrange
        $this->connectionDataRepository->setConnectionData($this->connectionData('sequra'));
        $this->connectionDataRepository->setConnectionData($this->connectionData('svea'));
        $this->connectionService->expects(self::exactly(2))->method('refreshCredentials');

        // Act
        $isOnboarding = $this->service->isOnboardingState();

        // Assert
        self::assertTrue($isOnboarding);
    }

    /**
     * @throws Exception
     */
    public function testIsNotOnboardingWithConnectionsAndWidgetSettings(): void
    {
        // Arrange
        $this->connectionDataRepository->setConnectionData($this->connectionData('sequra'));
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettings(true, true, true, '.test'));
        $this->connectionService->expects(self::once())->method('refreshCredentials');

        // Act
        $isOnboarding = $this->service->isOnboardingState();

        // Assert
        self::assertFalse($isOnboarding);
    }

    private function connectionData(string $deployment): ConnectionData
    {
        return new ConnectionData(
            BaseProxy::TEST_MODE,
            'logeecom',
            $deployment,
            new AuthorizationCredentials('username', 'password')
        );
    }

    /**
     * @throws Exception
     */
    public function testIsOnboardingWhenTheRefreshFailsWithoutWidgetSettings(): void
    {
        // Arrange
        $this->connectionDataRepository->setConnectionData($this->connectionData('sequra'));
        $this->connectionDataRepository->setConnectionData($this->connectionData('svea'));
        $this->connectionService->expects(self::exactly(2))->method('refreshCredentials')
            ->willThrowException(new WrongCredentialsException());

        // Act
        $isOnboarding = $this->service->isOnboardingState();

        // Assert
        self::assertTrue($isOnboarding);
    }

    /**
     * @throws Exception
     */
    public function testRefreshFailurePropagatesWithWidgetSettings(): void
    {
        // Arrange
        $this->connectionDataRepository->setConnectionData($this->connectionData('sequra'));
        $this->widgetSettingsRepository->setWidgetSettings(new WidgetSettings(true, true, true, '.test'));
        $this->connectionService->method('refreshCredentials')->willThrowException(new WrongCredentialsException());

        // Assert
        $this->expectException(WrongCredentialsException::class);

        // Act
        $this->service->isOnboardingState();
    }
}
